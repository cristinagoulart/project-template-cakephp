exports.command = function (username, password, callback) {
    var self = this;
    var url = self.launch_url + '/login';

    this.url(url)
        .waitForElementPresent('input#username', 2000)
        .setValue('input#username', username)
        .waitForElementPresent('input#password', 2000)
        .setValue('input#password', password)
        .submitForm('form')

    if (typeof callback === "function") {
        callback.call(self);
    }

    return this;
};
