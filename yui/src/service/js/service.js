
var AJAXBASE = M.cfg.wwwroot + '/mod/assign/submission/babelium/ajax.php';

M.assignsubmission_babelium = M.assignsubmission_babelium || {};
M.assignsubmission_babelium.babeliumapi = {
  init: function() {

  }

  search: function(request){
    var ajaxurl = AJAXBASE,
        config;

    config = {
      method: 'get',
      context: this,
      sync: false,
      data : {
          sesskey : M.cfg.sesskey,
          action : 'search',
          userid : this.get('userid'),
          q: request.q,
          lang: request.lang,
          difficulty: request.difficulty,
          type: request.type,
          situation: request.situation
      },
      on: {
          success: function(tid, response) {
              this.search_results(response.responseText);
          },
          failure: function(tid, response) {
              return new M.core.exception(response.responseText);
          }
      }
    }

    Y.io(ajaxurl, config);
  }
}
