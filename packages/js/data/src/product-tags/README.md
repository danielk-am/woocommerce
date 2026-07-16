# Product Tags Data Store

This data store provides functions to interact with the [Product Tag REST endpoints](https://developer.woocommerce.com/docs/apis/rest-api/v3/product-tags/).
Under the hood this data store makes use of the [CRUD data store](../crud/README.md).

**Note: This data store is listed as experimental still as it is still in active development.**

## Usage

This data store can be accessed under the `wc/admin/products/tags` name. It is recommended you make use of the export constant `EXPERIMENTAL_PRODUCT_TAGS_STORE_NAME`.

Example:

```js
import { EXPERIMENTAL_PRODUCT_TAGS_STORE_NAME } from '@woocommerce/data';
import { useDispatch } from '@wordpress/data';

function Component() {
	const actions = useDispatch(
		EXPERIMENTAL_PRODUCT_TAGS_STORE_NAME
	);
	actions.createProductTag( { name: 'test' } );
}
```

## Selections and actions

| Selector                            | Description                                                                                       |
| ----------------------------------- | ------------------------------------------------------------------------------------------------- |
| `getProductTag( id: number )`       | Gets a Product Tag by ID                                                                          |
| `getProductTagError( id )`          | Get the error for a failing GET product tag request.                                              |
| `getProductTags( query = {} )`      | Get all product tags, optionally by a specific query, see `Query` type in [types.ts](./types.ts). |
| `getProductTagsError( query = {} )` | Get the error for a GET request for all product tags.                                             |

Example usage: `wp.data.select( EXPERIMENTAL_PRODUCT_TAGS_STORE_NAME ).getProductTag( 3 );`

| Actions                                    | Method | Description                                                                  |
| ------------------------------------------ | ------ | ---------------------------------------------------------------------------- |
| `createProductTag( productTagObject )`     | POST   | Creates a product tag, see `ProductTag` in [types.ts](./types.ts) for values |
| `deleteProductTag( id )`                   | DELETE | Deletes a product tag by ID                                                  |
| `updateProductTag( id, productTagObject )` | PUT    | Updates a product tag, see `ProductTag` in [types.ts](./types.ts) for values |

Example usage: `wp.data.dispatch( EXPERIMENTAL_PRODUCT_TAGS_STORE_NAME ).updateProductTag( 3, { name: 'New name' } );`
