# Changelog

- Tested up to: WordPress 6.6.2

## v1.6.0

- Add: WeAreFrank! as supplier
- Change: refactor controller classes

## v1.5.0

- Add: implement IdpUserData for retrieving BSN

## v1.4.1

- Fix: check if $holder is array before using 'isSingleMultidimensionalArray' method when prefilling

## v1.4.0

- Add: VrijBRP as supplier
- Add: custom age check and municipality check GF fields
- Change: GF Addon fields + implement basic token usage
- Change: explode dot notated values when prefilling

## v1.3.0

- Add: check for eIDAS BSN
- Change: changelog formatting

## v1.2.1

- Add: README.md
- Change: update license to EUPL 1.2

## v1.2.0

- Add: personal data blocks

## v1.1

- Add: prefill all advanced date fields
- Change: clean-up/refactoring & run composer format script

## v1.0.17

- Add: disable prefilled form fields when using 'Openzaak' or 'PinkRoccade' as configured supplier

## v1.0.16

- Add: when a field is mapped but there is no value found, set field to read-only

## v1.0.15

- Change: mapping field 'woonplaats' to 'woonplaatsnaam'

## v1.0.14

- Add: implement API key general setting
- Change: gf-addon title and icon

## v1.0.13

- Change: retrieve bsn by DigiD session instead of value of form field

## v1.0.12

- Add: tooltip for the 'Passphrase' form setting
- Change: clean-up code

## v1.0.11

- Change: restore 'Passphrase' form setting, is only used when this setting has a value

## v1.0.10

- Fix: root path to certificates

## v1.0.9

- Add: logging to Microsoft Teams

## v1.0.8

- Change: supplement BSN when length is lower than required 9

## v1.0.7

- Change: CURL header x-doelbinding is optional
- Change: remove 'Passphrase' form setting

## v1.0.6

- Change: iConnect settings, enable selection of .crt and .cer files

## v1.0.5

- Change: plugin name

## v1.0.4

- Change: Apply the right plugin version

## v1.0.3

- Add: handle multidimensional arrays in response when retrieving linked values

## v1.0.2

- Add: expand selectable prefill fields in form field settings
- Add: return linked values with ucfirst() when form field type is 'text'
- Add: return linked values in dd-mm-yyyy notation when form field type is 'date'
- Add: expand parameters to request url
- Add: form field setting for expanding the result set

## v1.0.1

- Add: plugin description
- Add: retrieve BSN from DigiD session.
- Add: dependency Yard | GravityForms DigiD plugin

## v1.0.0

- Add: prefill general settings
- Add: prefill form settings
- Add: prefill form field settings
- Add: Prefill selected form fields on pre-render of form
