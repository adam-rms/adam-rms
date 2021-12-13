import axios, { CancelToken } from 'axios';
import { baseURL } from "../globals/variables";

/**
 * Get data from AdamRMS API
 * @param endpoint API endpoint
 * @param data any parameter data for the endpoint
 * @param cancelToken an Axios cancelToken 
 * @returns response data as an Object
 * @link https://adam-rms.com/docs/api/intro for V1 endpoints
 */
const Api = async (endpoint: string, data: {}, cancelToken: CancelToken) => {

    return (
        axios
            .get(baseURL + 'api/' + endpoint, {
                cancelToken: cancelToken,
                params: data
            })
            .then(function (response) {
                if (response.data['result'] == true){
                    return(response.data['response']);
                } else {
                    return (response.data);
                }
            })
            .catch(function (error) {
                if (axios.isCancel(error)) {
                    console.log("axios request cancelled", error.message, endpoint);
                } else {
                    console.log(error.response);
                }
            })
    );
}

export default Api;