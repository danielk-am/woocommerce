# Product Variations Data Store

This data store provides functions to interact with the [Product Variation REST endpoints](https://developer.woocommerce.com/docs/apis/rest-api/v3/product-variations/).
Under the hood this data store makes use of the [CRUD data store](../crud/README.md).

**Note: This data store is listed as experimental still as it is still in active development.**

## Usage

This data store can be accessed under the `wc/admin/products/variations` name. It is recommended you make use of the export constant `EXPERIMENTAL_PRODUCT_VARIATIONS_STORE_NAME`.

The `/wc/v3/products/{product_id}/variations` REST namespace contains URL parameters, include `product_id` in the query or data object passed to selectors and actions.

Example:

```js
import { EXPERIMENTAL_PRODUCT_VARIATIONS_STORE_NAME } from '@woocommerce/data';
import { useDispatch } from '@wordpress/data';

function Component() {
	const actions = useDispatch(
		EXPERIMENTAL_PRODUCT_VARIATIONS_STORE_NAME
	);
	actions.createProductVariation( { product_id: 1, name: 'test' } );
}
```

## Selections and actions

| Selector                                  | Description                                                                                             |
| ----------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| `getProductVariation( id: number )`       | Gets a Product Variation by ID                                                                          |
| `getProductVariationError( id )`          | Get the error for a failing GET product variation request.                                              |
| `getProductVariations( query = {} )`      | Get all product variations, optionally by a specific query, see `Query` type in [types.ts](./types.ts). |
| `getProductVariationsError( query = {} )` | Get the error for a GET request for all product variations.                                             |

Example usage: `wp.data.select( EXPERIMENTAL_PRODUCT_VARIATIONS_STORE_NAME ).getProductVariation( 3 );`

| Actions                                                | Method | Description                                                                              |
| ------------------------------------------------------ | ------ | ---------------------------------------------------------------------------------------- |
| `createProductVariation( productVariationObject )`     | POST   | Creates a product variation, see `ProductVariation` in [types.ts](./types.ts) for values |
| `deleteProductVariation( id )`                         | DELETE | Deletes a product variation by ID                                                        |
| `updateProductVariation( id, productVariationObject )` | PUT    | Updates a product variation, see `ProductVariation` in [types.ts](./types.ts) for values |

Example usage: `wp.data.dispatch( EXPERIMENTAL_PRODUCT_VARIATIONS_STORE_NAME ).updateProductVariation( 3, { name: 'New name' } );`
