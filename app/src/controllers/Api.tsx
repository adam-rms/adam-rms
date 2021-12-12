import axios, { CancelToken } from 'axios';
import { baseURL } from "../globals/variables";

//TODO remove the memory leak from async function by using axios cancel functionality 

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