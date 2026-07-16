# {{TITLE}}

This data store provides functions to interact with the [{{DOC_LINK_TEXT}}]({{DOC_URL}}).
Under the hood this data store makes use of the [CRUD data store](../crud/README.md).

**Note: This data store is listed as experimental still as it is still in active development.**

## Usage

This data store can be accessed under the `{{STORE_NAME}}` name. It is recommended you make use of the export constant `{{STORE_CONSTANT}}`.
{{URL_PARAMS_NOTE}}
Example:

```js
import { {{STORE_CONSTANT}} } from '@woocommerce/data';
import { useDispatch } from '@wordpress/data';

function Component() {
	const actions = useDispatch(
		{{STORE_CONSTANT}}
	);
	actions.create{{RESOURCE}}( {{CREATE_EXAMPLE_ARG}} );
}
```

## Selections and actions

{{SELECTORS_TABLE}}

Example usage: `wp.data.select( {{STORE_CONSTANT}} ).get{{RESOURCE}}( 3 );`

{{ACTIONS_TABLE}}

Example usage: `wp.data.dispatch( {{STORE_CONSTANT}} ).update{{RESOURCE}}( 3, { name: 'New name' } );`
