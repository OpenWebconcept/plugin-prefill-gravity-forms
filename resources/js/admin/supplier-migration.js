/**
 * Asks for confirmation on the add-on settings page when the supplier is changed from a V1 supplier to a V2
 * supplier, e.g. from VrijBRP to EnableU V2. The answer is stored in a hidden input and handled when saving.
 *
 * The config is provided by SupplierMigrationSettings::enqueueConfirmScript() as window.owcPrefillSupplierMigration.
 */
( function ( config ) {
	if ( ! config ) {
		return;
	}

	const form = document.getElementById( 'gform-settings' );

	if ( ! form ) {
		return;
	}

	const supplier = form.querySelector( `[name="${ config.supplierInput }"]` );
	const confirmed = form.querySelector( `[name="${ config.confirmedInput }"]` );

	if ( ! supplier || ! confirmed ) {
		return;
	}

	form.addEventListener( 'submit', () => {
		const isTarget = config.targets.includes( supplier.value );

		confirmed.value =
			isTarget &&
			window.confirm( config.message.replace( '%s', supplier.value ) )
				? '1'
				: '';
	} );
} )( window.owcPrefillSupplierMigration );
