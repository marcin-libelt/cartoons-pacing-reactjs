import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';

const reactApp = function(){
     return {
          init(element, parameter) {
               return ReactDOM.render(<App parameter={parameter}/>, document.getElementById(element));
          }
     }
}

define(reactApp);
