/*
  ext_log.js — ProInf.net — nov-2011

  Requiere: prototype.js, pi-extranet.js
*/

var Main = {

  init: function() {
    PI.Session.validate(function (ok) {
      if (ok) {
        Log.init();
      }
    });
  },

};

Event.observe(window, 'load', Main.init);

//-----------------------------------------------

var Log = {

  init: function() {
    $('file').value = Log.query('file', 'url.log');
    $('lines').value = Log.query('lines',10);
  },

  query: function(param, byDefault) {
    var value = PI.Util.queryString(param);
    return value || byDefault;
  },

};

/* */
