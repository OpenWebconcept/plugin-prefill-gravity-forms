# BRP Prefill Gravity Forms

This plug-in facilitates editors to configure form completion by establishing a link between form fields and BRP API data. When prefilling fields with a BSN number, the value is saved encrypted in the database, ensuring the security of stored data. Consequently, both the list and detail pages displaying form entries utilize encrypted values. The behavior can be adjusted using the 'owc_prefill_gravityforms_use_value_bsn_decrypted' filter by setting the return value to true. By using this filter the encrypted values are displayed decrypted. The value is always saved encrypted in the database!

## Example

```
add_filter('owc_prefill_gravityforms_use_value_bsn_decrypted', '__return_true');
```
