const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: async () => {
		const entries = await defaultConfig.entry();

		return {
			...entries,
			icons: './resources/scss/icons.scss',
			style: './resources/scss/index.scss',
		};
	},
};
