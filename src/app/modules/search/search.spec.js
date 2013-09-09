/**
 * Tests sit right alongside the file they are testing, which is more intuitive
 * and portable than separating `src` and `test` directories. Additionally, the
 * build process will exclude all `.spec.js` files from the build
 * automatically.
 */
describe( 'Search section', function() {
    beforeEach( module( 'Preslog.search' ) );

    it( 'should parse simple sql to jql, testing equals', inject( function() {
        var scope = {};
        ctrl = new SearchCtrl(scope);
        scope.sqlToJql('SELECT * FROM "LOGS" WHERE id = 1');
        expect(scope.jql).toBe('ID = 1');
    }));

    it( 'should parse simple sql to jql, testing not equals', inject( function() {
        var scope = {};
        ctrl = new SearchCtrl(scope);
        scope.sqlToJql('SELECT * FROM "LOGS" WHERE id != 1');
        expect(scope.jql).toBe('ID != 1');
    }));
});

