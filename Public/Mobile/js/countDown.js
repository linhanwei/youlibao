/**
 * Created by xiuxiu on 2016/6/29.
 */
function CountDown(options) {
    if ($.isPlainObject(options)) {
        $.extend(this, options);
    } else if ($.isNumeric(options)) {
        this.time = options;
    } else {
        this.time = 0;
    }
}
CountDown.prototype = {
    start: function() {
        return this.clear().run();
    },
    run: function() {
        var S = this;
        S.time--;
        if (S.time > 0) {
            S.id = setTimeout(function() {
                S.change();
                S.run();
            }, 1000);
        } else {
            S.end();
        }
        return this;
    },
    end: function() {
        return this;
    },
    change: function() {
        return this;
    },
    reset: function(time) {
        this.clear().time = time;
        return this;
    },
    clear: function() {
        clearTimeout(this.id);
        return this;
    }
};
