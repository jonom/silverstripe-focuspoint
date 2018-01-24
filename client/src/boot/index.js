/* global document */
import React from 'react';
import Injector from 'lib/Injector';
import registerComponents from './registerComponents';

document.addEventListener('DOMContentLoaded', () => {
  registerComponents();

  //TODO: Consider adding the focus-field directly at the UploadField level
  /*
  Injector.transform(
    'focuspoint-uploadfield',
    (updater) => {
    }
  );
  */
});

