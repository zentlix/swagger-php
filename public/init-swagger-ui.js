// This file is part of the API Platform project.
//
// (c) Kévin Dunglas <dunglas@gmail.com>
//
// For the full copyright and license information, please view the LICENSE
// file that was distributed with this source code.

function loadSwaggerUI(userOptions = {}) {
  const data = JSON.parse(document.getElementById('swagger-data').innerText);
  const defaultOptions = {
    spec: data.spec,
    dom_id: '#swagger-ui',
    validatorUrl: null,
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
    layout: 'StandaloneLayout'
  };
  const options = Object.assign({}, defaultOptions, userOptions);
    window.ui = SwaggerUIBundle(options);
}
