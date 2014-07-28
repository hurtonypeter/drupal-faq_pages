(function($) {
  "use strict";

  var guid = (function() {
    function s4() {
      return Math.floor((1 + Math.random()) * 0x10000)
              .toString(16)
              .substring(1);
    }
    return function() {
      return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
              s4() + '-' + s4() + s4() + s4();
    };
  })();

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

    $scope.model = {
      title: 'muhahhahaha',
      url: '/sdf',
      description: 'little descrp',
      blocks: [
        {id: 1, name: 'blokk1', description: 'blockdecrtiption1', terms: []},
        {id: 2, name: 'blocck2', description: 'blockdecrtiption2', terms: []},
        {id: 3, name: 'blokk3', description: 'blockdecrtiption3', terms: []}
      ]
    };

    $scope.newBlock = function() {
      $scope.model.blocks.push({
        id: null,
        name: '',
        description: '',
        terms: [],
      });
    };

    $scope.deleteBlock = function(id) {
      $scope.model.blocks.splice(id, 1);
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

    $scope.termUp = function(term, block) {
      var temp = $scope.model.blocks[block].terms[term - 1];
      $scope.model.blocks[block].terms[term - 1] = $scope.model.blocks[block].terms[term];
      $scope.model.blocks[block].terms[term] = temp;
    };

    $scope.termDown = function(term, block) {
      var temp = $scope.model.blocks[block].terms[term + 1];
      $scope.model.blocks[block].terms[term + 1] = $scope.model.blocks[block].terms[term];
      $scope.model.blocks[block].terms[term] = temp;
    };

    $scope.deleteTerm = function(term, block) {
      $scope.model.blocks[block].terms.splice(term, 1);
    };

    $scope.hideMe = function(terms) {
      return terms.length == 0;
    };

    $scope.savePage = function(){
      $http.post('/drupal8/faq/save-page', $scope.model, {
        headers: {
          'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
        }
      }).success(function(data) {
        console.log(data);
      });
    }
  });

})(jQuery);