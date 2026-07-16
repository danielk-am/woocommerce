/**
 * Generate a README.md for every data store in src/ that is built with
 * createCrudDataStore, from the template in bin/crud-readme.template.md.
 *
 * Dependency free, run it with plain node from the package root:
 *
 *     node bin/generate-crud-readmes.mjs           # write missing READMEs only
 *     node bin/generate-crud-readmes.mjs --force   # rewrite existing ones too
 *
 * or via the package script:
 *
 *     pnpm generate:readmes
 *
 * Store details (resource names, store name, REST namespace) are read from
 * each store's index.ts and constants.ts. The docs link is derived from the
 * plural resource name; verify it resolves when a new store is added.
 */

/**
 * External dependencies
 */
import fs from 'node:fs';
import path from 'node:path';
import url from 'node:url';

const packageDir = path.dirname(
	path.dirname( url.fileURLToPath( import.meta.url ) )
);
const srcDir = path.join( packageDir, 'src' );
const templatePath = path.join(
	packageDir,
	'bin',
	'crud-readme.template.md'
);
const force = process.argv.includes( '--force' );

const template = fs.readFileSync( templatePath, 'utf8' );

/**
 * Split a PascalCase name into its words, e.g. 'ProductTag' -> [ 'Product', 'Tag' ].
 *
 * @param {string} name PascalCase name.
 * @return {Array<string>} Words.
 */
function toWords( name ) {
	return name.split( /(?=[A-Z])/ );
}

/**
 * Render an aligned markdown table.
 *
 * @param {Array<string>}        header Header cells.
 * @param {Array<Array<string>>} rows   Body rows.
 * @return {string} Markdown table.
 */
function renderTable( header, rows ) {
	const widths = header.map( ( cell, i ) =>
		Math.max( cell.length, ...rows.map( ( row ) => row[ i ].length ) )
	);
	const line = ( cells ) =>
		'| ' +
		cells.map( ( cell, i ) => cell.padEnd( widths[ i ] ) ).join( ' | ' ) +
		' |';
	return [
		line( header ),
		line( widths.map( ( width ) => '-'.repeat( width ) ) ),
		...rows.map( line ),
	].join( '\n' );
}

/**
 * Pull a store's details out of its index.ts and constants.ts.
 *
 * @param {string} dir Store directory name inside src/.
 * @return {Object|null} Store details, or null when the store is not CRUD based.
 */
function parseStore( dir ) {
	const indexPath = path.join( srcDir, dir, 'index.ts' );
	if ( ! fs.existsSync( indexPath ) ) {
		return null;
	}
	const index = fs.readFileSync( indexPath, 'utf8' );
	if ( ! index.includes( 'createCrudDataStore' ) ) {
		return null;
	}

	const constantsPath = path.join( srcDir, dir, 'constants.ts' );
	const constants = fs.existsSync( constantsPath )
		? fs.readFileSync( constantsPath, 'utf8' )
		: '';

	const resource = index.match( /resourceName:\s*'([^']+)'/ );
	const plural = index.match( /pluralResourceName:\s*'([^']+)'/ );
	const storeConstant = index.match(
		/export const (EXPERIMENTAL_[A-Z0-9_]+_STORE_NAME) = STORE_NAME/
	);
	const storeName = constants.match( /STORE_NAME =\s*'([^']+)'/ );
	const namespace = constants.match( /_NAMESPACE =\s*'([^']+)'/ );

	if ( ! resource || ! plural || ! storeConstant || ! storeName || ! namespace ) {
		throw new Error(
			`Could not parse the ${ dir } store; generate its README manually or extend this script.`
		);
	}

	return {
		dir,
		resource: resource[ 1 ],
		plural: plural[ 1 ],
		storeConstant: storeConstant[ 1 ],
		storeName: storeName[ 1 ],
		namespace: namespace[ 1 ],
	};
}

/**
 * Render the README for one store.
 *
 * @param {Object} store Store details from parseStore.
 * @return {string} README contents.
 */
function renderReadme( store ) {
	const words = toWords( store.resource );
	const singular = words.join( ' ' );
	const singularLower = singular.toLowerCase();
	const pluralLower = toWords( store.plural ).join( ' ' ).toLowerCase();
	const article = /^[aeiou]/i.test( singular ) ? 'an' : 'a';
	const docSlug = toWords( store.plural ).join( '-' ).toLowerCase();
	const objectArg =
		words[ 0 ].toLowerCase() + words.slice( 1 ).join( '' ) + 'Object';

	const urlParams = [ ...store.namespace.matchAll( /\{(\w+)\}/g ) ].map(
		( match ) => match[ 1 ]
	);
	const urlParamsNote = urlParams.length
		? '\nThe `' +
		  store.namespace +
		  '` REST namespace contains URL parameters, include ' +
		  urlParams.map( ( param ) => '`' + param + '`' ).join( ' and ' ) +
		  ' in the query or data object passed to selectors and actions.\n'
		: '';
	const createExampleArg =
		'{ ' +
		urlParams.map( ( param ) => `${ param }: 1, ` ).join( '' ) +
		"name: 'test' }";

	const selectorsTable = renderTable(
		[ 'Selector', 'Description' ],
		[
			[
				`\`get${ store.resource }( id: number )\``,
				`Gets ${ article } ${ singular } by ID`,
			],
			[
				`\`get${ store.resource }Error( id )\``,
				`Get the error for a failing GET ${ singularLower } request.`,
			],
			[
				`\`get${ store.plural }( query = {} )\``,
				`Get all ${ pluralLower }, optionally by a specific query, see \`Query\` type in [types.ts](./types.ts).`,
			],
			[
				`\`get${ store.plural }Error( query = {} )\``,
				`Get the error for a GET request for all ${ pluralLower }.`,
			],
		]
	);

	const actionsTable = renderTable(
		[ 'Actions', 'Method', 'Description' ],
		[
			[
				`\`create${ store.resource }( ${ objectArg } )\``,
				'POST',
				`Creates ${ article } ${ singularLower }, see \`${ store.resource }\` in [types.ts](./types.ts) for values`,
			],
			[
				`\`delete${ store.resource }( id )\``,
				'DELETE',
				`Deletes ${ article } ${ singularLower } by ID`,
			],
			[
				`\`update${ store.resource }( id, ${ objectArg } )\``,
				'PUT',
				`Updates ${ article } ${ singularLower }, see \`${ store.resource }\` in [types.ts](./types.ts) for values`,
			],
		]
	);

	const replacements = {
		'{{TITLE}}': `${ toWords( store.plural ).join( ' ' ) } Data Store`,
		'{{DOC_LINK_TEXT}}': `${ singular } REST endpoints`,
		'{{DOC_URL}}': `https://developer.woocommerce.com/docs/apis/rest-api/v3/${ docSlug }/`,
		'{{STORE_NAME}}': store.storeName,
		'{{STORE_CONSTANT}}': store.storeConstant,
		'{{RESOURCE}}': store.resource,
		'{{CREATE_EXAMPLE_ARG}}': createExampleArg,
		'{{URL_PARAMS_NOTE}}': urlParamsNote,
		'{{SELECTORS_TABLE}}': selectorsTable,
		'{{ACTIONS_TABLE}}': actionsTable,
	};

	return Object.entries( replacements ).reduce(
		( readme, [ placeholder, value ] ) =>
			readme.replaceAll( placeholder, value ),
		template
	);
}

const stores = fs
	.readdirSync( srcDir, { withFileTypes: true } )
	.filter( ( entry ) => entry.isDirectory() && entry.name !== 'crud' )
	.map( ( entry ) => parseStore( entry.name ) )
	.filter( Boolean );

let written = 0;
for ( const store of stores ) {
	const readmePath = path.join( srcDir, store.dir, 'README.md' );
	if ( fs.existsSync( readmePath ) && ! force ) {
		console.log( `skip  ${ store.dir } (README.md exists, use --force)` );
		continue;
	}
	fs.writeFileSync( readmePath, renderReadme( store ) );
	console.log( `write ${ store.dir }/README.md` );
	written++;
}
console.log( `${ stores.length } CRUD stores, ${ written } README(s) written.` );
