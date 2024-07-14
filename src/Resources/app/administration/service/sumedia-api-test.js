const ApiService = Shopware.Classes.ApiService;
const { Application } = Shopware;

class ApiClient extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'sumedia-wbo') {
        super(httpClient, loginService, apiEndpoint);
    }

    check(values) {
        const headers = this.getBasicHeaders({});

        return this.httpClient
            .post('sumedia-wbo/checkConnection', values, headers)
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

Application.addServiceProvider('sumediaApiTest', (container) => {
    const initContainer = Application.getContainer('init');
    return new ApiClient(initContainer.httpClient, container.loginService);
});