/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "../../../node_modules/@guestyorg/tokenization-js/lib/esm/guesty-tokenization-js.js":
/*!******************************************************************************************!*\
  !*** ../../../node_modules/@guestyorg/tokenization-js/lib/esm/guesty-tokenization-js.js ***!
  \******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   loadScript: () => (/* binding */ loadScript)\n/* harmony export */ });\nvar findScriptElement = function (url) {\n    return document.querySelector(\"script[src=\\\"\".concat(url, \"\\\"]\"));\n};\nvar injectScriptElement = function (_a) {\n    var url = _a.url, sandbox = _a.sandbox, onSuccess = _a.onSuccess, onError = _a.onError;\n    var script = document.createElement('script');\n    script.src = url;\n    script.async = true;\n    script.onerror = onError;\n    script.onload = onSuccess;\n    if (sandbox) {\n        script.setAttribute('data-env', 'sandbox');\n    }\n    document.head.insertBefore(script, document.head.firstElementChild);\n};\n\nvar NAMESPACE = 'guestyTokenization';\nvar SCRIPT_URL = 'https://pay.guesty.com/tokenization/v1/init.js';\n\nvar loadScript = function (options) {\n    if (options === void 0) { options = {}; }\n    if (typeof window === 'undefined') {\n        return Promise.resolve(null);\n    }\n    var existingScript = findScriptElement(SCRIPT_URL);\n    var existingNamespace = window[NAMESPACE];\n    if (existingScript) {\n        if (existingNamespace) {\n            return Promise.resolve(existingNamespace);\n        }\n        existingScript.remove();\n    }\n    return new Promise(function (resolve, reject) {\n        var _a;\n        injectScriptElement({\n            url: SCRIPT_URL,\n            sandbox: (_a = options.sandbox) !== null && _a !== void 0 ? _a : false,\n            onSuccess: function () {\n                var newNamespace = window[NAMESPACE];\n                if (newNamespace) {\n                    resolve(newNamespace);\n                }\n                else {\n                    reject(new Error('Guesty Tokenization is not available'));\n                }\n            },\n            onError: function () {\n                reject(new Error(\"The script \".concat(SCRIPT_URL, \" failed to load\")));\n            },\n        });\n    });\n};\n\n\n\n\n//# sourceURL=webpack:///../../../node_modules/@guestyorg/tokenization-js/lib/esm/guesty-tokenization-js.js?");

/***/ }),

/***/ "./assets/js/guesty-payment.js":
/*!*************************************!*\
  !*** ./assets/js/guesty-payment.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _guestyorg_tokenization_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @guestyorg/tokenization-js */ \"../../../node_modules/@guestyorg/tokenization-js/lib/esm/guesty-tokenization-js.js\");\n\r\n\r\ndocument.addEventListener(\"DOMContentLoaded\", async function () {\r\n  const containerId = \"guesty-tokenization-container\";\r\n  const providerId = \"acct_1OloHTAcnyo9ow0l\"; // Replace with your actual provider ID\r\n\r\n  try {\r\n    // Load the Guesty Tokenization SDK\r\n    const guestyTokenization = await (0,_guestyorg_tokenization_js__WEBPACK_IMPORTED_MODULE_0__.loadScript)();\r\n    console.log(\"Guesty Tokenization JS SDK is loaded and ready to use\");\r\n\r\n    // Render the tokenization form\r\n    await guestyTokenization.render({\r\n      containerId: containerId,\r\n      providerId: providerId,\r\n    });\r\n    console.log(\"Guesty Tokenization form rendered successfully\");\r\n\r\n    // Handle form submission\r\n    document\r\n      .getElementById(\"pay-now\")\r\n      .addEventListener(\"click\", async function () {\r\n        try {\r\n          const paymentMethod = await guestyTokenization.submit();\r\n          console.log(\"Payment method received:\", paymentMethod);\r\n          // Process payment method via Guesty's API\r\n        } catch (e) {\r\n          console.error(\"Failed to submit the Guesty Tokenization form\", e);\r\n        }\r\n      });\r\n  } catch (error) {\r\n    console.error(\r\n      \"Failed to load the Guesty Tokenization JS SDK script\",\r\n      error\r\n    );\r\n  }\r\n});\r\n\n\n//# sourceURL=webpack:///./assets/js/guesty-payment.js?");

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
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./assets/js/guesty-payment.js");
/******/ 	
/******/ })()
;