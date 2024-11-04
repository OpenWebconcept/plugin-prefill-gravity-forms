/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/blocks/personal-data-table/edit.js":
/*!*********************************************************!*\
  !*** ./resources/js/blocks/personal-data-table/edit.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);

/**
 * WordPress dependencies
 */

const Edit = () => {
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...(0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps)()
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "container px-0"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InnerBlocks, {
    allowedBlocks: ['prefill-gravity-forms/personal-data-row'],
    template: [['prefill-gravity-forms/personal-data-row']],
    renderAppender: () => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InnerBlocks.ButtonBlockAppender, null)
  })));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Edit);

/***/ }),

/***/ "./resources/js/blocks/personal-data-table/index.js":
/*!**********************************************************!*\
  !*** ./resources/js/blocks/personal-data-table/index.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./edit */ "./resources/js/blocks/personal-data-table/edit.js");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./save */ "./resources/js/blocks/personal-data-table/save.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./block.json */ "./resources/js/blocks/personal-data-table/block.json");
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */



const {
  name,
  description,
  title,
  category,
  icon,
  attributes
} = _block_json__WEBPACK_IMPORTED_MODULE_3__;
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(name, {
  title,
  description,
  attributes,
  edit: _edit__WEBPACK_IMPORTED_MODULE_1__["default"],
  save: _save__WEBPACK_IMPORTED_MODULE_2__["default"],
  icon,
  category
});

/***/ }),

/***/ "./resources/js/blocks/personal-data-table/personal-data-row/config/personalDataOptions.js":
/*!*************************************************************************************************!*\
  !*** ./resources/js/blocks/personal-data-table/personal-data-row/config/personalDataOptions.js ***!
  \*************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
const personalDataOptions = [{
  value: '',
  label: 'Selecteer persoonsgegevens',
  disabled: true
}, {
  label: 'Burgerservicenummer (BSN)',
  value: 'burgerservicenummer'
}, {
  label: 'Geslacht',
  value: 'geslachtsaanduiding'
}, {
  label: 'Leeftijd',
  value: 'leeftijd'
}, {
  label: 'Achternaam',
  value: 'naam.geslachtsnaam'
}, {
  label: 'Voorletters',
  value: 'naam.voorletters'
}, {
  label: 'Voornaam',
  value: 'naam.voornaam'
}, {
  label: 'Voornamen',
  value: 'naam.voornamen'
}, {
  label: 'Geboortedatum',
  value: 'geboorte.datum.datum'
}, {
  label: 'Straat',
  value: 'verblijfplaats.straat'
}, {
  label: 'Huisnummer',
  value: 'verblijfplaats.huisnummer'
}, {
  label: 'Postcode',
  value: 'verblijfplaats.postcode'
}, {
  label: 'Woonplaats',
  value: 'verblijfplaats.woonplaats'
}];
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (personalDataOptions);

/***/ }),

/***/ "./resources/js/blocks/personal-data-table/personal-data-row/config/supplierOptions.js":
/*!*********************************************************************************************!*\
  !*** ./resources/js/blocks/personal-data-table/personal-data-row/config/supplierOptions.js ***!
  \*********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
const supplierOptions = [{
  value: '',
  label: 'Selecteer een leverancier',
  disabled: true
}, {
  label: 'EnableU',
  value: 'EnableU'
}, {
  label: 'OpenZaak',
  value: 'OpenZaak'
}, {
  label: 'PinkRoccade',
  value: 'PinkRoccade'
}, {
  label: 'VrijBRP',
  value: 'VrijBRP'
}, {
  label: 'WeAreFrank',
  value: 'WeAreFrank'
}];
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (supplierOptions);

/***/ }),

/***/ "./resources/js/blocks/personal-data-table/personal-data-row/edit.js":
/*!***************************************************************************!*\
  !*** ./resources/js/blocks/personal-data-table/personal-data-row/edit.js ***!
  \***************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _config_personalDataOptions__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./config/personalDataOptions */ "./resources/js/blocks/personal-data-table/personal-data-row/config/personalDataOptions.js");
/* harmony import */ var _config_supplierOptions__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./config/supplierOptions */ "./resources/js/blocks/personal-data-table/personal-data-row/config/supplierOptions.js");

/**
 * WordPress dependencies
 */






/**
 * Internal dependencies
 */


function Edit({
  attributes,
  setAttributes,
  clientId
}) {
  const {
    selectedSupplier,
    selectedOption,
    htmlElement,
    isChildOfTable
  } = attributes;
  const {
    blockParents
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.useSelect)(select => ({
    blockParents: select('core/block-editor').getBlockNamesByClientId(select('core/block-editor').getBlockParents(clientId))
  }));
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_5__.useEffect)(() => {
    if (blockParents.length > 0 && 'prefill-gravity-forms/personal-data-table' === blockParents[blockParents.length - 1]) {
      setAttributes({
        isChildOfTable: true
      });
    }
  }, [blockParents, setAttributes]);
  const handleSupplierChange = value => {
    const selectedSupplierData = {
      value,
      label: _config_supplierOptions__WEBPACK_IMPORTED_MODULE_7__["default"].find(option => option.value === value).label
    };
    setAttributes({
      selectedSupplier: selectedSupplierData
    });
  };
  const handlePersonalDataChange = value => {
    const selectedOptionData = {
      value,
      label: _config_personalDataOptions__WEBPACK_IMPORTED_MODULE_6__["default"].find(option => option.value === value).label
    };
    setAttributes({
      selectedOption: selectedOptionData
    });
  };
  const handleElementChange = value => {
    setAttributes({
      htmlElement: value
    });
  };
  const uncapitalize = str => {
    if (!str) return str;
    return str.charAt(0).toLowerCase() + str.slice(1);
  };
  const DynamicElement = htmlElement || 'div';
  const selectSupplierControl = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: "Leverancier",
    value: selectedSupplier.value,
    options: _config_supplierOptions__WEBPACK_IMPORTED_MODULE_7__["default"],
    onChange: handleSupplierChange
  });
  const selectPersonalDataControl = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: "Automatisch invullen",
    value: selectedOption.value,
    options: _config_personalDataOptions__WEBPACK_IMPORTED_MODULE_6__["default"],
    onChange: handlePersonalDataChange
  });
  const selectHtmlElementControl = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SelectControl, {
    label: "HTML Element",
    value: htmlElement,
    options: [{
      label: '<div>',
      value: 'div'
    }, {
      label: '<p>',
      value: 'p'
    }, {
      label: '<span>',
      value: 'span'
    }],
    onChange: handleElementChange
  });
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...(0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.useBlockProps)()
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.InspectorControls, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TabPanel, {
    activeClass: "active-tab",
    tabs: [{
      name: 'settings',
      title: 'Settings'
    }]
  }, tab => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.PanelBody, {
    initialOpen: true
  }, tab.name === 'settings' && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, selectSupplierControl, selectPersonalDataControl, !isChildOfTable && selectHtmlElementControl)))), undefined === selectedOption.value || '' === selectedOption.value ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, selectPersonalDataControl) : (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "row"
  }, isChildOfTable && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "col-6 font-weight-bold"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, selectedOption.label)), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "col-6"
  }, "[", uncapitalize(selectedOption.label), "]"))));
}

/***/ }),

/***/ "./resources/js/blocks/personal-data-table/personal-data-row/index.js":
/*!****************************************************************************!*\
  !*** ./resources/js/blocks/personal-data-table/personal-data-row/index.js ***!
  \****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./edit */ "./resources/js/blocks/personal-data-table/personal-data-row/edit.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./block.json */ "./resources/js/blocks/personal-data-table/personal-data-row/block.json");
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */


const {
  name,
  description,
  title,
  category,
  icon,
  attributes
} = _block_json__WEBPACK_IMPORTED_MODULE_2__;
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(name, {
  title,
  description,
  attributes,
  edit: _edit__WEBPACK_IMPORTED_MODULE_1__["default"],
  icon,
  category
});

/***/ }),

/***/ "./resources/js/blocks/personal-data-table/save.js":
/*!*********************************************************!*\
  !*** ./resources/js/blocks/personal-data-table/save.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);

/**
 * WordPress dependencies
 */

const Save = () => {
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ..._wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps.save()
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("table", {
    className: "table"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InnerBlocks.Content, null)));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Save);

/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "./resources/js/blocks/personal-data-table/block.json":
/*!************************************************************!*\
  !*** ./resources/js/blocks/personal-data-table/block.json ***!
  \************************************************************/
/***/ ((module) => {

module.exports = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"prefill-gravity-forms/personal-data-table","version":"0.1.0","title":"Profielpagina","category":"embed","icon":"list-view","description":"Maak een profielpagina met persoonsgegevens van de ingelogde gebruiker.","example":{},"supports":{"html":false},"textdomain":"prefill-gravity-forms","editorScript":"file:./index.js"}');

/***/ }),

/***/ "./resources/js/blocks/personal-data-table/personal-data-row/block.json":
/*!******************************************************************************!*\
  !*** ./resources/js/blocks/personal-data-table/personal-data-row/block.json ***!
  \******************************************************************************/
/***/ ((module) => {

module.exports = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"prefill-gravity-forms/personal-data-row","version":"0.1.0","title":"Persoonsgegevens","category":"embed","icon":"id","description":"Toon persoonsgegevens van de ingelogde gebruiker.","example":{},"supports":{"html":false},"attributes":{"selectedSupplier":{"type":"object","default":{"value":"","label":""}},"selectedOption":{"type":"object","default":{"value":"","label":""}},"isChildOfTable":{"type":"boolean","default":false},"htmlElement":{"type":"string","default":"p"}},"textdomain":"prefill-gravity-forms","editorScript":"file:./index.js","render":"file:./render.php"}');

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!**************************************!*\
  !*** ./resources/js/blocks/index.js ***!
  \**************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _personal_data_table_index__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./personal-data-table/index */ "./resources/js/blocks/personal-data-table/index.js");
/* harmony import */ var _personal_data_table_personal_data_row_index__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./personal-data-table/personal-data-row/index */ "./resources/js/blocks/personal-data-table/personal-data-row/index.js");


})();

/******/ })()
;
//# sourceMappingURL=blocks.js.map