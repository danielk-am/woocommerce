# Product Attributes Data Store

This data store provides functions to interact with the [Product Attribute REST endpoints](https://developer.woocommerce.com/docs/apis/rest-api/v3/product-attributes/).
Under the hood this data store makes use of the [CRUD data store](../crud/README.md).

**Note: This data store is listed as experimental still as it is still in active development.**

## Usage

This data store can be accessed under the `wc/admin/products/attributes` name. It is recommended you make use of the export constant `EXPERIMENTAL_PRODUCT_ATTRIBUTES_STORE_NAME`.

Example:

```js
import { EXPERIMENTAL_PRODUCT_ATTRIBUTES_STORE_NAME } from '@woocommerce/data';
import { useDispatch } from '@wordpress/data';

function Component() {
	const actions = useDispatch(
		EXPERIMENTAL_PRODUCT_ATTRIBUTES_STORE_NAME
	);
	actions.createProductAttribute( { name: 'test' } );
}
```

## Selections and actions

| Selector                                  | Description                                                                                             |
| ----------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| `getProductAttribute( id: number )`       | Gets a Product Attribute by ID                                                                          |
| `getProductAttributeError( id )`          | Get the error for a failing GET product attribute request.                                              |
| `getProductAttributes( query = {} )`      | Get all product attributes, optionally by a specific query, see `Query` type in [types.ts](./types.ts). |
| `getProductAttributesError( query = {} )` | Get the error for a GET request for all product attributes.                                             |

Example usage: `wp.data.select( EXPERIMENTAL_PRODUCT_ATTRIBUTES_STORE_NAME ).getProductAttribute( 3 );`

| Actions                                                | Method | Description                                                                              |
| ------------------------------------------------------ | ------ | ---------------------------------------------------------------------------------------- |
| `createProductAttribute( productAttributeObject )`     | POST   | Creates a product attribute, see `ProductAttribute` in [types.ts](./types.ts) for values |
| `deleteProductAttribute( id )`                         | DELETE | Deletes a product attribute by ID                                                        |
| `updateProductAttribute( id, productAttributeObject )` | PUT    | Updates a product attribute, see `ProductAttribute` in [types.ts](./types.ts) for values |

Example usage: `wp.data.dispatch( EXPERIMENTAL_PRODUCT_ATTRIBUTES_STORE_NAME ).updateProductAttribute( 3, { name: 'New name' } );`
