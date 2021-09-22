define([
    'uiComponent',
    'ko'
], function (Component, ko) {

    return Component.extend({
        initialize: function () {
            this._super();

            this.populateUi();

            this.gameplay();
        },
        populateUi: function () {

            this.userActions = ko.observableArray(['ping', 'pong']);
            this.selectedAction = ko.observable(this.initialMove);
            this.aiMove = ko.observable();
        },
        gameplay: function () {
            var self = this;

            ko.computed(function handleMove()
            {
                if (self.selectedAction() === self.userActions()[0]) {
                    self.aiMove(self.userActions()[1]);
                }
                else {
                    self.aiMove(self.userActions()[0]);
                }
            });
        }
    });
});
