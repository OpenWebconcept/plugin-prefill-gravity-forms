/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TabPanel,
	TextControl,
	SelectControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import personalDataOptions from './config/personalDataOptions';
import supplierOptions from './config/supplierOptions';

export default function Edit( { attributes, setAttributes, clientId } ) {
	const { selectedSupplier, goalBinding, processing, selectedOption, htmlElement, isChildOfTable } =
		attributes;

	const { blockParents } = useSelect( ( select ) => ( {
		blockParents: select( 'core/block-editor' ).getBlockNamesByClientId(
			select( 'core/block-editor' ).getBlockParents( clientId )
		),
	} ) );

	useEffect( () => {
		if (
			blockParents.length > 0 &&
			'owc-prefill-gravity-forms/personal-data-table' ===
				blockParents[ blockParents.length - 1 ]
		) {
			setAttributes( { isChildOfTable: true } );
		}
	}, [ blockParents, setAttributes ] );

	const supplierVersion = supplierOptions.find(
		( option ) => option.value === selectedSupplier.value
	)?.version;

	// Only show the options of the API version of the supplier, the selected option is kept to avoid an empty select.
	const availablePersonalDataOptions = personalDataOptions
		.filter(
			( option ) =>
				! option.version ||
				! supplierVersion ||
				option.version === supplierVersion ||
				option.value === selectedOption.value
		)
		.map( ( { label, value, disabled } ) => ( { label, value, disabled } ) );

	// Labels saved by previous versions could contain a version suffix, e.g. 'Straat (V2)'.
	const selectedOptionLabel = ( selectedOption.label ?? '' ).replace(
		/\s*\(V\d+\)$/,
		''
	);

	const handleSupplierChange = ( value ) => {
		const selectedSupplierData = {
			value,
			label: supplierOptions.find( ( option ) => option.value === value )
				.label,
		};
		setAttributes( { selectedSupplier: selectedSupplierData } );
	};

	const handlePersonalDataChange = ( value ) => {
		const selectedOptionData = {
			value,
			label: personalDataOptions.find(
				( option ) => option.value === value
			).label,
		};
		setAttributes( { selectedOption: selectedOptionData } );
	};

	const handleElementChange = ( value ) => {
		setAttributes( { htmlElement: value } );
	};

	const uncapitalize = ( str ) => {
		if ( ! str ) return str;
		return str.charAt( 0 ).toLowerCase() + str.slice( 1 );
	};

	const selectSupplierControl = (
		<SelectControl
			help="Selecteer de juiste leverancier die ingesteld staat in de algemene instellingen."
			label="Leverancier"
			value={ selectedSupplier.value }
			options={ supplierOptions }
			onChange={ handleSupplierChange }
		/>
	);

	const goalBindingControl = (
		<TextControl
			help="Algemene doelbinding voor verzoeken zonder specifieke doelbinding. Beschrijft waarom de persoonsgegevens worden opgevraagd en op basis van welke wettelijke taak of bevoegdheid de organisatie dit doet. De mogelijke waarden zijn implementatieafhankelijk en worden door de leverancier bepaald."
			label="Doelbinding (v2)"
			value={ goalBinding }
			onChange={ ( value ) => {
				setAttributes( { goalBinding: value } );
			} }
		/>
	);

	const processingControl = (
		<TextControl
			help="Beschrijft voor welk werkproces of welke processtap de BRP-gegevens worden opgevraagd. De mogelijke waarden zijn implementatieafhankelijk en dienen door de leverancier te worden aangeleverd."
			label="Verwerking (v2)"
			value={ processing }
			onChange={ ( value ) => {
				setAttributes( { processing: value } );
			} }
		/>
	);

	const selectPersonalDataControl = (
		<SelectControl
			label="Automatisch invullen"
			value={ selectedOption.value }
			options={ availablePersonalDataOptions }
			onChange={ handlePersonalDataChange }
		/>
	);

	const selectHtmlElementControl = (
		<SelectControl
			label="HTML Element"
			value={ htmlElement }
			options={ [
				{
					label: '<div>',
					value: 'div',
				},
				{
					label: '<p>',
					value: 'p',
				},
				{
					label: '<span>',
					value: 'span',
				},
			] }
			onChange={ handleElementChange }
		/>
	);

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<TabPanel
					activeClass="active-tab"
					tabs={ [
						{
							name: 'settings',
							title: 'Settings',
						},
					] }
				>
					{ ( tab ) => (
						<PanelBody initialOpen={ true }>
							{ tab.name === 'settings' && (
								<>
									{ selectSupplierControl }
									{ goalBindingControl }
									{ processingControl }
									{ selectPersonalDataControl }
									{ ! isChildOfTable &&
										selectHtmlElementControl }
								</>
							) }
						</PanelBody>
					) }
				</TabPanel>
			</InspectorControls>
			{ undefined === selectedOption.value ||
			'' === selectedOption.value ? (
				<p>{ selectPersonalDataControl }</p>
			) : (
				<>
					<div className="row">
						{ isChildOfTable && (
							<div className="col-6 font-weight-bold">
								<p>{ selectedOptionLabel }</p>
							</div>
						) }
						<div className="col-6">
							[{ uncapitalize( selectedOptionLabel ) }]
						</div>
					</div>
				</>
			) }
		</div>
	);
}
