describe('UsersController::login(): ', () => {
    before(function (browser, done) {
        done();
    });

    after(function (browser, done) {
        browser.end(function () {
            done();
        });
    });

    afterEach(function (browser, done) {
        done();
    });

    beforeEach(function (browser, done) {
        done();
    });

    it('Admin login with correct credentials', (browser) => {
        browser
            .login(process.env.DEV_USER, process.env.DEV_PASS)
            .assert.elementPresent('nav');
    });
    it('Admin logout via URL', (browser) => {
        browser
            .url(browser.launch_url + '/users/logout')
            .assert.urlContains('/login');
    });
    it('Admin login with wrong credentials', (browser) => {
        browser
            .login(process.env.DEV_USER, 'this is not a valid password ever')
            .assert.urlContains('/login');
    });
});
