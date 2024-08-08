/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import Edit from './edit';
import metadata from './block.json';

const { name, description, title, category, icon, attributes } = metadata;

registerBlockType( name, {
	title,
	description,
	attributes,
	edit: Edit,
	icon,
	category,
} );
