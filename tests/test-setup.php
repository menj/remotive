<?php
/**
 * Setup migrations: the contact address correction.
 * Run: php tests/test-setup.php
 */

require __DIR__ . '/bootstrap.php';
require dirname( __DIR__ ) . '/inc/setup/site-setup.php';

// The retired .com address is replaced, whatever its case or spacing.
foreach ( array( 'hello@remotivemedia.com', ' Hello@RemotiveMedia.com ' ) as $old ) {
	t_reset();
	$GLOBALS['T']['options']['remotive_theme_options'] = array( 'contact_email' => $old, 'legal_name' => 'Keep me' );
	t_ok( remotive_correct_contact_email(), "'$old' replaced" );
	$saved = get_option( 'remotive_theme_options' );
	t_eq( $saved['contact_email'], 'hello@remotivemedia.asia', "'$old' now .asia" );
	t_eq( $saved['legal_name'], 'Keep me', 'other saved options untouched' );
}

// An address an administrator chose on purpose is left alone.
t_reset();
$GLOBALS['T']['options']['remotive_theme_options'] = array( 'contact_email' => 'sales@example.com' );
t_ok( ! remotive_correct_contact_email(), 'custom address not changed' );
t_eq( get_option( 'remotive_theme_options' )['contact_email'], 'sales@example.com', 'custom address kept' );

// Nothing saved yet: nothing to do, and the shipped default is .asia.
t_reset();
t_ok( ! remotive_correct_contact_email(), 'no saved options, no change' );

// The migration is registered at the schema version that ships it.
t_ok( version_compare( REMOTIVE_SETUP_SCHEMA, '1.103.1', '>=' ), 'schema version covers the migration' );

t_done( 'setup' );
