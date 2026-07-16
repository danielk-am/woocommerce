# Product Attribute Terms Data Store

This data store provides functions to interact with the [Product Attribute Term REST endpoints](https://developer.woocommerce.com/docs/apis/rest-api/v3/product-attribute-terms/).
Under the hood this data store makes use of the [CRUD data store](../crud/README.md).

**Note: This data store is listed as experimental still as it is still in active development.**

## Usage

This data store can be accessed under the `wc/admin/products/attributes/terms` name. It is recommended you make use of the export constant `EXPERIMENTAL_PRODUCT_ATTRIBUTE_TERMS_STORE_NAME`.

The `/wc/v3/products/attributes/{attribute_id}/terms` REST namespace contains URL parameters, include `attribute_id` in the query or data object passed to selectors and actions.

Example:

```js
import { EXPERIMENTAL_PRODUCT_ATTRIBUTE_TERMS_STORE_NAME } from '@woocommerce/data';
import { useDispatch } from '@wordpress/data';

function Component() {
	const actions = useDispatch(
		EXPERIMENTAL_PRODUCT_ATTRIBUTE_TERMS_STORE_NAME
	);
	actions.createProductAttributeTerm( { attribute_id: 1, name: 'test' } );
}
```

## Selections and actions

| Selector                                      | Description                                                                                                  |
| --------------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| `getProductAttributeTerm( id: number )`       | Gets a Product Attribute Term by ID                                                                          |
| `getProductAttributeTermError( id )`          | Get the error for a failing GET product attribute term request.                                              |
| `getProductAttributeTerms( query = {} )`      | Get all product attribute terms, optionally by a specific query, see `Query` type in [types.ts](./types.ts). |
| `getProductAttributeTermsError( query = {} )` | Get the error for a GET request for all product attribute terms.                                             |

Example usage: `wp.data.select( EXPERIMENTAL_PRODUCT_ATTRIBUTE_TERMS_STORE_NAME ).getProductAttributeTerm( 3 );`

| Actions                                                        | Method | Description                                                                                       |
| -------------------------------------------------------------- | ------ | ------------------------------------------------------------------------------------------------- |
| `createProductAttributeTerm( productAttributeTermObject )`     | POST   | Creates a product attribute term, see `ProductAttributeTerm` in [types.ts](./types.ts) for values |
| `deleteProductAttributeTerm( id )`                             | DELETE | Deletes a product attribute term by ID                                                            |
| `updateProductAttributeTerm( id, productAttributeTermObject )` | PUT    | Updates a product attribute term, see `ProductAttributeTerm` in [types.ts](./types.ts) for values |

Example usage: `wp.data.dispatch( EXPERIMENTAL_PRODUCT_ATTRIBUTE_TERMS_STORE_NAME ).updateProductAttributeTerm( 3, { name: 'New name' } );`
