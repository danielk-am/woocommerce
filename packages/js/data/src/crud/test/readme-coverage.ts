/**
 * External dependencies
 */
import fs from 'fs';
import path from 'path';

const srcDir = path.join( __dirname, '..', '..' );

// Every data store built with createCrudDataStore should document itself.
// Missing READMEs can be generated with `node bin/generate-crud-readmes.mjs`.
describe( 'CRUD data store READMEs', () => {
	const crudStoreDirs = fs
		.readdirSync( srcDir, { withFileTypes: true } )
		.filter( ( entry ) => entry.isDirectory() && entry.name !== 'crud' )
		.map( ( entry ) => entry.name )
		.filter( ( name ) => {
			const indexPath = path.join( srcDir, name, 'index.ts' );
			return (
				fs.existsSync( indexPath ) &&
				fs
					.readFileSync( indexPath, 'utf8' )
					.includes( 'createCrudDataStore' )
			);
		} );

	it( 'should find the CRUD data stores', () => {
		expect( crudStoreDirs ).toContain( 'product-shipping-classes' );
	} );

	it.each( crudStoreDirs )( '%s should have a README.md', ( dir ) => {
		expect( fs.existsSync( path.join( srcDir, dir, 'README.md' ) ) ).toBe(
			true
		);
	} );
} );
