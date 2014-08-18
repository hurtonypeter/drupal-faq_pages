(function($) {
  "use strict";

  console.log(drupalSettings);

  var app = angular.module('probaApp', ['ngDragDrop']);
  app.value('$', $);

  //config start and end symbol not cause conflict with twig
  app.config(function($interpolateProvider) {
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
  });

  app.controller('probaCtrl', function($scope, $http) {

    $scope.terms = $.map(drupalSettings.term_model, function(value, index) {
      return [value];
    });

    $scope.model = drupalSettings.edit_model;
    console.log($scope.model);

    /**
     * Block organizing functions
     */
    $scope.newBlock = function() {
      $scope.model.blocks.push({
        id: null,
        name: '',
        topics: [],
      });
    };

    $scope.deleteBlock = function(block) {
      $scope.model.blocks.splice(block, 1);
    };

    $scope.blockUp = function(block) {
      var temp = $scope.model.blocks[block - 1];
      $scope.model.blocks[block - 1] = $scope.model.blocks[block];
      $scope.model.blocks[block] = temp;
    };

    $scope.blockDown = function(block) {
      var temp = $scope.model.blocks[block + 1];
      $scope.model.blocks[block + 1] = $scope.model.blocks[block];
      $scope.model.blocks[block] = temp;
    };

    /**
     * Topic organizing functions
     */
    $scope.newTopic = function(block) {
      $scope.model.blocks[block].topics.push({
        toid: null,
        name: '',
        description: '',
        terms: []
      });
    }

    $scope.deleteTopic = function(topic, block) {
      $scope.model.blocks[block].topics.splice(topic, 1);
    }

    $scope.topicUp = function(topic, block) {
      var temp = $scope.model.blocks[block].topics[topic - 1];
      $scope.model.blocks[block].topics[topic - 1] = $scope.model.blocks[block].topics[topic];
      $scope.model.blocks[block].topics[topic] = temp;
    }

    $scope.topicDown = function(topic, block) {
      var temp = $scope.model.blocks[block].topics[topic + 1];
      $scope.model.blocks[block].topics[topic + 1] = $scope.model.blocks[block].topics[topic];
      $scope.model.blocks[block].topics[topic] = temp;
    }

    /*
     * Term organizing functions
     */
    $scope.termUp = function(term, topic, block) {
      var temp = $scope.model.blocks[block].topics[topic].terms[term - 1];
      $scope.model.blocks[block].topics[topic].terms[term - 1] = $scope.model.blocks[block].topics[topic].terms[term];
      $scope.model.blocks[block].topics[topic].terms[term] = temp;
    };

    $scope.termDown = function(term, topic, block) {
      var temp = $scope.model.blocks[block].topics[topic].terms[term + 1];
      $scope.model.blocks[block].topics[topic].terms[term + 1] = $scope.model.blocks[block].topics[topic].terms[term];
      $scope.model.blocks[block].topics[topic].terms[term] = temp;
    };

    $scope.deleteTerm = function(term, topic, block) {
      $scope.model.blocks[block].topics[topic].terms.splice(term, 1);
    };

    $scope.hideMe = function(terms) {
      return terms.length == 0;
    };

    $scope.savePage = function() {
      $http.post(drupalSettings.path.basePath + 'faq/save-page', $scope.model, {
        headers: {
          'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
        }
      }).success(function(data) {
        console.log(data);
        if (!data.error) {
          $scope.model = data.data;
          $('.messages-container').prepend($('<div class="messages messages--status" \
            role="contentinfo" aria-label="Status message"><h2 class="visually-hidden">\
            Status message</h2>Page is successfully saved!</div>').fadeIn('slow')).delay(5000).queue(function(next) {
            $('.messages').fadeOut('slow', function() {
              $(this).remove();
            });
            next();
          });
        }
      });
    }
  });

})(jQuery);