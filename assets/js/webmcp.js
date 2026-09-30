/**
 * Remotive Media WebMCP adapter.
 *
 * Registers tools only when the browser supplies document.modelContext.
 * Lead tools prepare visible forms but intentionally never submit them.
 */
(function () {
	'use strict';

	var config = window.RemotiveWebMCP || {};

	function setFieldValue(field, value) {
		if (!field || typeof value !== 'string') {
			return;
		}

		field.value = value;
		field.dispatchEvent(new Event('input', { bubbles: true }));
		field.dispatchEvent(new Event('change', { bubbles: true }));
	}

	function activateForm(form, focusField) {
		form.classList.add('rm-webmcp-active');
		form.scrollIntoView({ behavior: 'smooth', block: 'center' });

		window.setTimeout(function () {
			if (focusField) {
				focusField.focus({ preventScroll: true });
			}
		}, 350);
	}

	function annotateSearchForm() {
		var form = document.querySelector('.wp-block-search form, form[role="search"]');
		var input;

		if (!form) {
			return;
		}

		form.setAttribute('toolname', 'search-remotive-content-form');
		form.setAttribute('tooldescription', 'Search Remotive Media public pages, insights, services, and case studies.');
		form.setAttribute('toolautosubmit', '');
		input = form.querySelector('input[type="search"], input[name="s"]');

		if (input) {
			input.setAttribute('toolparamdescription', 'The words or phrase to search for.');
		}
	}

	async function registerTool(definition) {
		try {
			await document.modelContext.registerTool(definition);
		} catch (error) {
			if (window.console && typeof window.console.warn === 'function') {
				window.console.warn('Remotive WebMCP could not register ' + definition.name + '.', error);
			}
		}
	}

	function registerSiteTools() {
		registerTool({
			name: 'search-remotive-site',
			title: 'Search Remotive Media',
			description: 'Search Remotive Media public pages, services, case studies, and insights. Use this instead of guessing a URL.',
			// Reads only, and returns page copy the site controls but the
			// agent should not treat as instructions.
			annotations: { readOnlyHint: true, untrustedContentHint: true },
			inputSchema: {
				type: 'object',
				properties: {
					query: {
						type: 'string',
						description: 'Words or a phrase describing the information sought.',
						minLength: 1,
						maxLength: 200
					},
					limit: {
						type: 'integer',
						description: 'Maximum results to return.',
						minimum: 1,
						maximum: 10,
						default: 5
					}
				},
				required: ['query'],
				additionalProperties: false
			},
			execute: async function (input, options) {
				var endpoint = new URL(config.searchEndpoint, window.location.origin);
				var response;

				endpoint.searchParams.set('query', input.query.trim());
				endpoint.searchParams.set('limit', String(input.limit || 5));
				response = await fetch(endpoint.toString(), {
					method: 'GET',
					credentials: 'same-origin',
					headers: { Accept: 'application/json' },
					signal: options && options.signal ? options.signal : undefined
				});

				if (!response.ok) {
					throw new Error('Site search failed with HTTP status ' + response.status + '.');
				}

				return response.json();
			}
		});

		registerTool({
			name: 'get-remotive-navigation',
			title: 'Remotive Media destinations',
			description: 'Get the canonical destinations for Remotive Media services, work, insights, contact, and home.',
			annotations: { readOnlyHint: true, untrustedContentHint: false },
			inputSchema: {
				type: 'object',
				properties: {},
				additionalProperties: false
			},
			execute: function () {
				return {
					home: config.homeUrl,
					services: config.servicesUrl,
					work: config.workUrl,
					insights: config.blogUrl,
					contact: config.contactUrl
				};
			}
		});

		registerTool({
			name: 'open-remotive-page',
			title: 'Open a Remotive Media page',
			description: 'Open a same-site Remotive Media URL, normally one returned by another Remotive tool.',
			annotations: { readOnlyHint: false, untrustedContentHint: false },
			inputSchema: {
				type: 'object',
				properties: {
					url: {
						type: 'string',
						description: 'A same-origin HTTP or HTTPS URL to open.'
					}
				},
				required: ['url'],
				additionalProperties: false
			},
			execute: function (input) {
				var target = new URL(input.url, window.location.origin);

				if (target.origin !== window.location.origin || !/^https?:$/.test(target.protocol)) {
					throw new Error('Only same-site HTTP or HTTPS URLs may be opened.');
				}

				window.setTimeout(function () {
					window.location.assign(target.href);
				}, 0);

				return { status: 'navigating', url: target.href };
			}
		});
	}

	function registerContactTool() {
		var form = document.querySelector('.rm-about-form');
		var email;

		if (!form) {
			return;
		}

		email = form.querySelector('input[name="email"]');

		registerTool({
			name: 'prepare-remotive-contact',
			title: 'Prepare the contact form',
			description: 'Fill the visible Remotive Media contact form for the visitor to review and submit. This tool never sends the form.',
			// Not read-only: it writes into the page. It still cannot submit.
			annotations: { readOnlyHint: false, untrustedContentHint: false },
			inputSchema: {
				type: 'object',
				properties: {
					name: { type: 'string', description: 'Visitor name.', maxLength: 120 },
					email: { type: 'string', description: 'Visitor email address.', format: 'email', maxLength: 254 },
					message: { type: 'string', description: 'Project or enquiry details.', maxLength: 5000 }
				},
				required: ['email', 'message'],
				additionalProperties: false
			},
			execute: function (input) {
				setFieldValue(form.querySelector('input[name="name"]'), input.name || '');
				setFieldValue(email, input.email);
				setFieldValue(form.querySelector('textarea[name="message"]'), input.message);
				activateForm(form, email);

				return {
					status: 'awaiting-user-review',
					submitted: false,
					message: 'The visible contact form has been prepared. Ask the visitor to review it and press Send message.'
				};
			}
		});
	}

	function registerAuditTool() {
		var form = document.querySelector('.rm-cta__form');
		var email;

		if (!form) {
			return;
		}

		email = form.querySelector('input[name="email"]');

		registerTool({
			name: 'prepare-remotive-audit-request',
			title: 'Prepare the free audit request',
			description: 'Fill the visible free-audit request with an email address for the visitor to review and submit. This tool never sends the form.',
			annotations: { readOnlyHint: false, untrustedContentHint: false },
			inputSchema: {
				type: 'object',
				properties: {
					email: { type: 'string', description: 'Visitor business email address.', format: 'email', maxLength: 254 }
				},
				required: ['email'],
				additionalProperties: false
			},
			execute: function (input) {
				setFieldValue(email, input.email);
				activateForm(form, email);

				return {
					status: 'awaiting-user-review',
					submitted: false,
					message: 'The visible audit request has been prepared. Ask the visitor to review it and press Get my free audit.'
				};
			}
		});
	}

	var registered = false;

	function initialise() {
		annotateSearchForm();

		// registerTool is exposed on secure contexts only, and rejects a
		// second registration of the same name, so both are checked before
		// any call rather than relying on the catch in registerTool().
		if (registered || !window.isSecureContext) {
			return;
		}

		if (!document.modelContext || typeof document.modelContext.registerTool !== 'function') {
			return;
		}

		registered = true;

		registerSiteTools();
		registerContactTool();
		registerAuditTool();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initialise, { once: true });
	} else {
		initialise();
	}
}());
