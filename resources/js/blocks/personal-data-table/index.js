/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import Edit from './edit';
import Save from './save';
import metadata from './block.json';

const { name, description, title, category, icon, attributes } = metadata;

registerBlockType( name, {
	title,
	description,
	attributes,
	edit: Edit,
	save: Save,
	icon,
	category,
} );
