/**
 * WordPress dependencies
 */
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

const Save = () => {
	return (
		<div { ...useBlockProps.save() }>
			<table className="table">
				<InnerBlocks.Content />
			</table>
		</div>
	);
};

export default Save;
