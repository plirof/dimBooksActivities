var auth = (function() {
    var apiPath = (function() {
        var path = window.location.pathname;
        if (path.indexOf('/admin/') !== -1) {
            return '../api/login.php';
        }
        return 'api/login.php';
    })();
    
    return {
        login: function(username, password, callback) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', apiPath, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (callback) {
                        callback(response);
                    }
                }
            };
            
            xhr.send(JSON.stringify({
                username: username,
                password: password
            }));
        },
        
        logout: function(callback) {
            var xhr = new XMLHttpRequest();
            xhr.open('DELETE', apiPath, true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (callback) {
                        callback(response);
                    }
                }
            };
            
            xhr.send();
        },
        
        checkLogin: function(callback) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', apiPath, true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (callback) {
                        callback(response);
                    }
                }
            };
            
            xhr.send();
        }
    };
})();
