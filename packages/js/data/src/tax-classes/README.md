# Tax Classes Data Store

This data store provides functions to interact with the [Tax Class REST endpoints](https://developer.woocommerce.com/docs/apis/rest-api/v3/tax-classes/).
Under the hood this data store makes use of the [CRUD data store](../crud/README.md).

**Note: This data store is listed as experimental still as it is still in active development.**

## Usage

This data store can be accessed under the `experimental/wc/admin/tax-classes` name. It is recommended you make use of the export constant `EXPERIMENTAL_TAX_CLASSES_STORE_NAME`.

Example:

```js
import { EXPERIMENTAL_TAX_CLASSES_STORE_NAME } from '@woocommerce/data';
import { useDispatch } from '@wordpress/data';

function Component() {
	const actions = useDispatch(
		EXPERIMENTAL_TAX_CLASSES_STORE_NAME
	);
	actions.createTaxClass( { name: 'test' } );
}
```

## Selections and actions

| Selector                           | Description                                                                                      |
| ---------------------------------- | ------------------------------------------------------------------------------------------------ |
| `getTaxClass( id: number )`        | Gets a Tax Class by ID                                                                           |
| `getTaxClassError( id )`           | Get the error for a failing GET tax class request.                                               |
| `getTaxClasses( query = {} )`      | Get all tax classes, optionally by a specific query, see `Query` type in [types.ts](./types.ts). |
| `getTaxClassesError( query = {} )` | Get the error for a GET request for all tax classes.                                             |

Example usage: `wp.data.select( EXPERIMENTAL_TAX_CLASSES_STORE_NAME ).getTaxClass( 3 );`

| Actions                                | Method | Description                                                              |
| -------------------------------------- | ------ | ------------------------------------------------------------------------ |
| `createTaxClass( taxClassObject )`     | POST   | Creates a tax class, see `TaxClass` in [types.ts](./types.ts) for values |
| `deleteTaxClass( id )`                 | DELETE | Deletes a tax class by ID                                                |
| `updateTaxClass( id, taxClassObject )` | PUT    | Updates a tax class, see `TaxClass` in [types.ts](./types.ts) for values |

Example usage: `wp.data.dispatch( EXPERIMENTAL_TAX_CLASSES_STORE_NAME ).updateTaxClass( 3, { name: 'New name' } );`
