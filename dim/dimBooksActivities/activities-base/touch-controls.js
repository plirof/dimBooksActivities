var TouchControls = {
    show: true,
    _cssInjected: false,
    _defaultTheme: {
        bg: '#e3f2fd',
        border: '#90caf9',
        active: '#bbdefb',
        text: '#1565c0'
    },
    _injectCSS: function(theme) {
        if (this._cssInjected) return;
        this._cssInjected = true;
        var t = theme || this._defaultTheme;
        var style = document.createElement('style');
        style.textContent =
            '.tc-dpad{' +
                'display:flex;justify-content:center;gap:6px;margin-top:10px;flex-wrap:wrap;' +
            '}' +
            '.tc-cross{' +
                'display:grid;' +
                'grid-template-areas:"_ up _" "left center right" "_ down _";' +
                'grid-template-columns:repeat(3,52px);' +
                'grid-template-rows:repeat(3,52px);' +
                'gap:4px;justify-content:center;margin-top:10px;' +
            '}' +
            '.tc-btn{' +
                'width:52px;height:52px;border-radius:14px;' +
                'border:2px solid ' + t.border + ';' +
                'background:' + t.bg + ';' +
                'font-size:1.4em;color:' + t.text + ';cursor:pointer;' +
                'display:flex;align-items:center;justify-content:center;' +
                'user-select:none;-webkit-user-select:none;touch-action:manipulation;' +
                'transition:background .1s;' +
            '}' +
            '.tc-btn:active,.tc-btn.tc-active{' +
                'background:' + t.active + ';' +
            '}' +
            '@media(max-width:500px){' +
                '.tc-btn{width:44px;height:44px;font-size:1.2em;border-radius:12px}' +
                '.tc-cross{grid-template-columns:repeat(3,46px);grid-template-rows:repeat(3,46px)}' +
            '}';
        document.head.appendChild(style);
    },
    create: function(opts) {
        if (!this.show) return null;
        if (!opts || !opts.container) return null;

        var theme = opts.theme || this._defaultTheme;
        this._injectCSS(theme);

        var directions = opts.directions || ['up', 'down', 'left', 'right'];
        var is4Dir = directions.indexOf('up') !== -1 || directions.indexOf('down') !== -1;
        var wrapper = document.createElement('div');

        var btns = {};
        var labels = { up: '\u25B2', down: '\u25BC', left: '\u25C0', right: '\u25B6' };

        if (is4Dir) {
            wrapper.className = 'tc-cross';
            var positions = {
                up: { area: 'up', order: ['_up_', 'left_center_right', '_down_'] },
                left: { area: 'left' },
                right: { area: 'right' },
                down: { area: 'down' }
            };
            var gridItems = [
                { dir: null, area: '_up_' },
                { dir: 'up', area: 'up' },
                { dir: null, area: '_up_' },
                { dir: 'left', area: 'left' },
                { dir: null, area: 'center' },
                { dir: 'right', area: 'right' },
                { dir: null, area: '_down_' },
                { dir: 'down', area: 'down' },
                { dir: null, area: '_down_' }
            ];
            for (var i = 0; i < gridItems.length; i++) {
                var item = gridItems[i];
                if (item.dir && directions.indexOf(item.dir) !== -1) {
                    var btn = document.createElement('button');
                    btn.className = 'tc-btn';
                    btn.textContent = labels[item.dir];
                    btn.dataset.dir = item.dir;
                    btn.style.gridArea = item.area;
                    btn.style.justifySelf = 'center';
                    btn.style.alignSelf = 'center';
                    wrapper.appendChild(btn);
                    btns[item.dir] = btn;
                } else {
                    var spacer = document.createElement('div');
                    spacer.style.gridArea = item.area;
                    wrapper.appendChild(spacer);
                }
            }
        } else {
            wrapper.className = 'tc-dpad';
            for (var j = 0; j < directions.length; j++) {
                var d = directions[j];
                var b = document.createElement('button');
                b.className = 'tc-btn';
                b.textContent = labels[d];
                b.dataset.dir = d;
                wrapper.appendChild(b);
                btns[d] = b;
            }
        }

        var self = this;
        var dirKeys = Object.keys(btns);
        for (var k = 0; k < dirKeys.length; k++) {
            (function(dir) {
                var btnEl = btns[dir];
                function startDir(e) {
                    e.preventDefault();
                    btnEl.classList.add('tc-active');
                    if (opts.onPress) opts.onPress(dir);
                }
                function stopDir(e) {
                    e.preventDefault();
                    btnEl.classList.remove('tc-active');
                    if (opts.onRelease) opts.onRelease(dir);
                }
                btnEl.addEventListener('mousedown', startDir);
                btnEl.addEventListener('mouseup', stopDir);
                btnEl.addEventListener('mouseleave', stopDir);
                btnEl.addEventListener('touchstart', startDir, { passive: false });
                btnEl.addEventListener('touchend', stopDir, { passive: false });
                btnEl.addEventListener('touchcancel', stopDir, { passive: false });
            })(dirKeys[k]);
        }

        opts.container.appendChild(wrapper);
        return { wrapper: wrapper, buttons: btns };
    }
};
