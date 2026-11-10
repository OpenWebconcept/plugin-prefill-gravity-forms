/**
 * The version is the HaalCentraal BRP API version of the supplier, used to filter the personal data options.
 */
const supplierOptions = [
	{
		value: '',
		label: 'Selecteer een leverancier',
		disabled: true,
	},
	{
		label: 'EnableU',
		value: 'EnableU',
		version: '1',
	},
	{
		label: 'EnableU V2',
		value: 'EnableUV2',
		version: '2',
	},
	{
		label: 'OpenZaak',
		value: 'OpenZaak',
		version: '1',
	},
	{
		label: 'PinkRoccade',
		value: 'PinkRoccade',
		version: '1',
	},
	{
		label: 'PinkRoccadeV2',
		value: 'PinkRoccadeV2',
		version: '2',
	},
	{
		label: 'VrijBRP',
		value: 'VrijBRP',
		version: '1',
	},
	{
		label: 'WeAreFrank',
		value: 'WeAreFrank',
		version: '2',
	},
];

export default supplierOptions;
