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
import { useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import personalDataOptions from './config/personalDataOptions';
import supplierOptions from './config/supplierOptions';

export default function Edit( { attributes, setAttributes, clientId } ) {
	const { selectedSupplier, selectedOption, htmlElement, isChildOfTable } =
		attributes;

	const { blockParents } = useSelect( ( select ) => ( {
		blockParents: select( 'core/block-editor' ).getBlockNamesByClientId(
			select( 'core/block-editor' ).getBlockParents( clientId )
		),
	} ) );

	useEffect( () => {
		if (
			blockParents.length > 0 &&
			'prefill-gravity-forms/personal-data-table' ===
				blockParents[ blockParents.length - 1 ]
		) {
			setAttributes( { isChildOfTable: true } );
		}
	}, [ blockParents, setAttributes ] );

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

	const DynamicElement = htmlElement || 'div';

	const selectSupplierControl = (
		<SelectControl
			label="Leverancier"
			value={ selectedSupplier.value }
			options={ supplierOptions }
			onChange={ handleSupplierChange }
		/>
	);

	const selectPersonalDataControl = (
		<SelectControl
			label="Automatisch invullen"
			value={ selectedOption.value }
			options={ personalDataOptions }
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
								<p>{ selectedOption.label }</p>
							</div>
						) }
						<div className="col-6">
							[{ uncapitalize( selectedOption.label ) }]
						</div>
					</div>
				</>
			) }
		</div>
	);
}
