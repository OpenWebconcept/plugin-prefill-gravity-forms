/**
 * WordPress dependencies
 */
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

const Edit = () => {
	return (
		<div { ...useBlockProps() }>
			<div className="container px-0">
				<InnerBlocks
					allowedBlocks={ [
						'owc-prefill-gravity-forms/personal-data-row',
					] }
					template={ [
						[ 'owc-prefill-gravity-forms/personal-data-row' ],
					] }
					renderAppender={ () => <InnerBlocks.ButtonBlockAppender /> }
				/>
			</div>
		</div>
	);
};

export default Edit;
