# Changelog

- Tested up to: WordPress 6.7.2

## v1.7.7

- Fix: text domain was loaded too early

## v1.7.6

- Fix: prevent error log spam regarding null being passed to dirname because of a missing variable
- Change: add editorconfig line that reflects the currently in use code style to prevent whitespace issues

## v1.7.5

- Change: clear values of custom fields when validations fail for conditional logic to work

## v1.7.4

- Change: hide custom GF fields success views when no success message has been configured

## v1.7.3

- Fix: set field properties age check validation messages

## v1.7.2

- Fix: prefill children information

## v1.7.1

- Add: max age check

## v1.7.0

- Add: children to the mapping options
- Add: 'huisletter' as prefill option to supplier options WeAreFrank

## v1.6.11

- Change: hide empty values personal-data-row Gutenberg block

## v1.6.10

- Fix: remove typehinting on status param in logError()

## v1.6.9

- Add: configurable api-key header in GF addon settings

## v1.6.8

- Add: timeout option to CURL args

## v1.6.7

- Fix: check if DigiD userdata is not null before retrieving BSN

## v1.6.6

- Add: add help property to supplier select control in personal-data-row block
- Change: remove static response from controller classes

## v1.6.5

- Fix: return null when controller class, based on selected supplier, is not found

## v1.6.4

- Fix: personal data row block so it supports multiple suppliers
- Fix: check if multi-dimensional array contains only one array

## v1.6.3

- Add: check response of curl request and return exception when request failed

## v1.6.2

- Change: logging uses message and status code inside controllers

## v1.6.1

- Change: WeAreFrank! GF fields mapping options

## v1.6.0

- Add: WeAreFrank! as supplier
- Change: refactor controller classes
- Change: English translatable strings to Dutch

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
