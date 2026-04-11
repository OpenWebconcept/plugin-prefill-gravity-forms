/**
 * OWC Prefill Gravity Forms — Configuration Manager
 *
 * Handles the interactive configuration manager on the plugin settings page.
 * Data is passed from PHP via `window.owcPgConfigManager` (wp_localize_script).
 */

import $ from 'jquery';

const data            = window.owcPgConfigManager || {};
const i18n            = data.i18n || {};
const supplierChoices = data.supplierChoices || [];
let configs           = ( data.configs || [] ).slice();

const $manager   = $( '#owc-pg-config-manager' );
const $table     = $manager.find( '#owc-pg-config-table' );
const $list      = $manager.find( '#owc-pg-config-list' );
const $editor    = $manager.find( '#owc-pg-config-editor' );
const $emptyMsg  = $manager.find( '#owc-pg-config-empty' );
const $jsonInput = $manager.find( '#owc-pg-configurations-json' );
const $errors    = $editor.find( '.owc-pg-validation-errors' );

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function generateId() {
	return 'cfg-' + Date.now().toString( 36 ) + Math.random().toString( 36 ).slice( 2, 7 );
}

function syncJson() {
	$jsonInput.val( JSON.stringify( configs ) );
}

function supplierLabel( slug ) {
	const choice = supplierChoices.find( ( c ) => c.value === slug );
	return choice ? choice.label : slug;
}

function escHtml( str ) {
	return $( '<div>' ).text( str ).html();
}

function submitSettingsForm() {
	// Click the GF save button so GF's own handlers run normally (spinner, POST
	// value inclusion). The capture-phase listener below handles sync / validation
	// before any GF handler sees the click.
	const btn = $jsonInput.closest( 'form' ).find( '[name="gform-settings-save"]' )[ 0 ];
	if ( btn ) {
		btn.click();
	}
}

function showErrors( messages ) {
	$errors.find( 'p' ).html( messages.join( '<br>' ) );
	$errors.addClass( 'is-visible' );
}

function hideErrors() {
	$errors.removeClass( 'is-visible' );
}

// ---------------------------------------------------------------------------
// Supplier select population
// ---------------------------------------------------------------------------

function populateSupplierSelect() {
	const $select = $( '#owc-pg-cfg-supplier' );
	$select.empty();
	$select.append(
		$( '<option>' )
			.val( '' )
			.text( i18n.selectSupplier || '— Selecteer een leverancier —' )
	);
	supplierChoices.forEach( ( choice ) => {
		$select.append( $( '<option>' ).val( choice.value ).text( choice.label ) );
	} );
}

// ---------------------------------------------------------------------------
// Table rendering
// ---------------------------------------------------------------------------

function renderTable() {
	$list.empty();

	if ( ! configs.length ) {
		$table.hide();
		$emptyMsg.show();
		return;
	}

	$table.show();
	$emptyMsg.hide();

	configs.forEach( ( cfg ) => {
		$list.append(
			'<tr data-config-id="' + escHtml( cfg.id ) + '">' +
			'<td>' + escHtml( cfg.label || '' ) + '</td>' +
			'<td>' + escHtml( supplierLabel( cfg.supplier || '' ) ) + '</td>' +
			'<td>' + escHtml( cfg[ 'base-url' ] || '' ) + '</td>' +
			'<td>' +
			'<button type="button" class="button button-small owc-pg-edit-config">' +
				escHtml( i18n.edit || 'Bewerken' ) +
			'</button> ' +
			'<button type="button" class="button button-small owc-pg-delete-config">' +
				escHtml( i18n.delete || 'Verwijderen' ) +
			'</button>' +
			'</td></tr>'
		);
	} );
}

// ---------------------------------------------------------------------------
// Editor: populate / read
// ---------------------------------------------------------------------------

function populateEditor( cfg ) {
	cfg = cfg || {};

	$( '#owc-pg-editing-id' ).val( cfg.id || generateId() );
	$( '#owc-pg-cfg-label' ).val( cfg.label || '' );
	$( '#owc-pg-cfg-supplier' ).val( cfg.supplier || '' );
	$( '#owc-pg-cfg-base-url' ).val( cfg[ 'base-url' ] || '' );
	$( '#owc-pg-cfg-oin' ).val( cfg[ 'oin-number' ] || '' );
	$( '#owc-pg-cfg-processing' ).val( cfg.processing || '' );
	$( '#owc-pg-cfg-user' ).val( cfg.user || '' );

	const apiAuth = cfg[ 'api-use-authentication' ] === '1';
	$( '#owc-pg-cfg-api-auth' ).prop( 'checked', apiAuth );
	$( '.owc-pg-api-auth-field' ).toggle( apiAuth );
	$( '#owc-pg-cfg-api-key' ).val( cfg[ 'api-key' ] || '' );
	$( '#owc-pg-cfg-api-key-header' ).val( cfg[ 'api-key-header-name' ] || 'x-api-key' );
	$( '#owc-pg-cfg-api-username' ).val( cfg[ 'api-basic-token-username' ] || '' );
	$( '#owc-pg-cfg-api-password' ).val( cfg[ 'api-basic-token-password' ] || '' );

	const ssl = cfg[ 'use-ssl-certificates' ] === '1';
	$( '#owc-pg-cfg-ssl' ).prop( 'checked', ssl );
	$( '.owc-pg-ssl-field' ).toggle( ssl );
	$( '#owc-pg-cfg-pub-cert' ).val( cfg[ 'public-certificate' ] || '' );
	$( '#owc-pg-cfg-priv-cert' ).val( cfg[ 'private-certificate' ] || '' );
	$( '#owc-pg-cfg-supplier-cert' ).val( cfg[ 'supplier-certificate' ] || '' );
	$( '#owc-pg-cfg-passphrase' ).val( cfg.passphrase || '' );

	$( '#owc-pg-cfg-logging' ).prop( 'checked', cfg[ 'logging-enabled' ] === '1' );
	$( '#owc-pg-cfg-user-model' ).prop( 'checked', cfg[ 'enable-user-model' ] === '1' );
}

function readEditor() {
	return {
		id:                         $( '#owc-pg-editing-id' ).val(),
		label:                      $( '#owc-pg-cfg-label' ).val().trim(),
		supplier:                   $( '#owc-pg-cfg-supplier' ).val(),
		'base-url':                 $( '#owc-pg-cfg-base-url' ).val().trim(),
		'oin-number':               $( '#owc-pg-cfg-oin' ).val().trim(),
		processing:                 $( '#owc-pg-cfg-processing' ).val().trim(),
		user:                       $( '#owc-pg-cfg-user' ).val().trim(),
		'api-use-authentication':   $( '#owc-pg-cfg-api-auth' ).is( ':checked' ) ? '1' : '0',
		'api-key':                  $( '#owc-pg-cfg-api-key' ).val().trim(),
		'api-key-header-name':      $( '#owc-pg-cfg-api-key-header' ).val().trim() || 'x-api-key',
		'api-basic-token-username': $( '#owc-pg-cfg-api-username' ).val().trim(),
		'api-basic-token-password': $( '#owc-pg-cfg-api-password' ).val(),
		'use-ssl-certificates':     $( '#owc-pg-cfg-ssl' ).is( ':checked' ) ? '1' : '0',
		'public-certificate':       $( '#owc-pg-cfg-pub-cert' ).val(),
		'private-certificate':      $( '#owc-pg-cfg-priv-cert' ).val(),
		'supplier-certificate':     $( '#owc-pg-cfg-supplier-cert' ).val(),
		passphrase:                 $( '#owc-pg-cfg-passphrase' ).val(),
		'logging-enabled':          $( '#owc-pg-cfg-logging' ).is( ':checked' ) ? '1' : '0',
		'enable-user-model':        $( '#owc-pg-cfg-user-model' ).is( ':checked' ) ? '1' : '0',
	};
}

function validate( cfg ) {
	const errors = [];
	if ( ! cfg.label )         errors.push( i18n.errorLabel    || 'Naam is verplicht.' );
	if ( ! cfg.supplier )      errors.push( i18n.errorSupplier || 'Leverancier is verplicht.' );
	if ( ! cfg[ 'base-url' ] ) errors.push( i18n.errorBaseUrl  || 'Basis URL is verplicht.' );
	return errors;
}

function openEditor( cfg ) {
	hideErrors();
	populateEditor( cfg );
	$( '#owc-pg-editor-title' ).text(
		cfg && cfg.id
			? ( i18n.editConfig || 'Configuratie bewerken' )
			: ( i18n.newConfig  || 'Nieuwe configuratie' )
	);
	$editor.show();
	$editor[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
}

// ---------------------------------------------------------------------------
// Initialise
// ---------------------------------------------------------------------------

populateSupplierSelect();
syncJson();
renderTable();

// ---------------------------------------------------------------------------
// Event bindings
// ---------------------------------------------------------------------------

$( '#owc-pg-add-config' ).on( 'click', () => {
	openEditor( null );
} );

$list.on( 'click', '.owc-pg-edit-config', function () {
	const id  = $( this ).closest( 'tr' ).data( 'config-id' );
	const cfg = configs.find( ( c ) => c.id === id );
	openEditor( cfg );
} );

$list.on( 'click', '.owc-pg-delete-config', function () {
	const id    = $( this ).closest( 'tr' ).data( 'config-id' );
	const label = $( this ).closest( 'tr' ).find( 'td:first' ).text();

	// eslint-disable-next-line no-alert
	if ( ! window.confirm( ( i18n.confirmDelete || 'Weet je zeker dat je de configuratie wilt verwijderen?' ) + ' "' + label + '"' ) ) {
		return;
	}

	configs = configs.filter( ( c ) => c.id !== id );
	// Hide the editor so the capture handler below skips validation.
	$editor.hide();
	hideErrors();
	submitSettingsForm();
} );

// Capture-phase listener on the GF save button.
//
// Using capture (third argument = true) means this runs BEFORE GF's jQuery
// bubble-phase handlers — so we can block the click (and the spinner) when
// the editor has validation errors, and sync the JSON before GF sees the submit.
const gfSaveBtn = $jsonInput.closest( 'form' ).find( '[name="gform-settings-save"]' )[ 0 ];

if ( gfSaveBtn ) {
	gfSaveBtn.addEventListener( 'click', function ( event ) {
		if ( $editor.is( ':visible' ) ) {
			const cfg    = readEditor();
			const errors = validate( cfg );

			if ( errors.length ) {
				showErrors( errors );
				// Stop here — prevents GF's handlers and the spinner from running.
				event.preventDefault();
				event.stopImmediatePropagation();
				return;
			}

			const idx = configs.findIndex( ( c ) => c.id === cfg.id );

			if ( idx >= 0 ) {
				configs[ idx ] = cfg;
			} else {
				configs.push( cfg );
			}
		}

		// Always sync before letting GF submit the form.
		syncJson();
	}, true );
}

$( '#owc-pg-cancel-config' ).on( 'click', () => {
	$editor.hide();
	hideErrors();
} );

$( '#owc-pg-cfg-api-auth' ).on( 'change', function () {
	$( '.owc-pg-api-auth-field' ).toggle( this.checked );
} );

$( '#owc-pg-cfg-ssl' ).on( 'change', function () {
	$( '.owc-pg-ssl-field' ).toggle( this.checked );
} );
