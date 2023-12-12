# Changelog

-   Tested up to: WordPress 6.3.2

## v1.2.0

### Feat

-   Personal data blocks

## v1.1

### Feat

-   Prefill all advanced date fields.
-	Small clean-up/refactoring & run composer format script.

## v1.0.17

### Feat

-   Disable prefilled form fields when using 'Openzaak' or 'PinkRoccade' as configured supplier.

## v1.0.16

### Feat

-   When a field is mapped but there is no value found, set field to read-only.

## v1.0.15

### Refactor

-   Mapping field 'woonplaats' to 'woonplaatsnaam'.

## v1.0.14

### Feat

-   Implement API key general setting.

### Refactor

-   gf-addon title and icon

## v1.0.13

### Refactor

-   Retrieve bsn by DigiD session instead of value of form field.

## v1.0.12

### Refactor

-   Add tooltip for the 'Passphrase' form setting.
-   Clean-up code

## v1.0.11

### Refactor

-   Restore 'Passphrase' form setting, is only used when this setting has a value.

## v1.0.10

### Fix

-   Root path to certificates

## v1.0.9

### Feat

-   Add logging to Microsoft Teams.

## v1.0.8

### Refactor

-   Supplement BSN when length is lower than required 9.

## v1.0.7

### Refactor

-   CURL header x-doelbinding is optional.
-   Remove 'Passphrase' form setting.

## v1.0.6

### Refactor

-   iConnect settings, enable selection of .crt and .cer files.

## v1.0.5

### Refactor

-   Update plugin name.

## v1.0.4

### Refactor

-   Apply the right plug-in version.

## v1.0.3

### Add

-   Handle multidimensional arrays in response when retrieving linked values.

## v1.0.2

### Add

-   Expand selectable prefill fields in form field settings.
-   Return linked values with ucfirst() when form field type is 'text'.
-   Return linked values in dd-mm-yyyy notation when form field type is 'date'.
-   Add expand parameters to request url.
-   Add form field setting for expanding the result set.

## v1.0.1

### Add

-   Add plugin description.
-   Retrieve BSN from DigiD session.
-   Add depedency Yard | GravityForms DigiD plugin.

## v1.0.0

### Add

-   Add prefill general settings.
-   Add prefill form settings.
-   Add prefill form field settings.
-   Prefill selected form fields on pre-render of form.
