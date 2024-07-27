const ApiService = Shopware.Classes.ApiService;

export default class SumediaWinestroApiService extends ApiService {
    constructor(httpClient, loginService) {
        super(httpClient, loginService);
    }

    post(url, values) {
        return this.httpClient
            .post(url, values, this.getBasicHeaders({ headers: {'Authorization' : 'Bearer ' + this.loginService.getToken()}}))
            .then((response) => {
                return SumediaWinestroApiService.handleResponse(response);
            });
    }
}