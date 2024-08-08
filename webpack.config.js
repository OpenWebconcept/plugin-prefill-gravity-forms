const defaultConfig = require( '@wordpress/scripts/config/webpack.config' ); // Original config from the @wordpress/scripts package.

module.exports = {
	...defaultConfig,
	entry: {
		blocks: [ './resources/js/blocks/index.js' ],
		icons: [ './resources/scss/icons.scss' ],
		style: [ './resources/scss/index.scss' ],
	},
};
