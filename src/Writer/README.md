# Writer API

Public API of the _Long Essay Assessment Service_ to integrate the writer frontend in a PHP based _System_ (e.g. a Learning Management System ). The writer frontend is a Single Page Application (SPA) written in JavaScript. it is included as a pre-built Node.js module in this service. All communication between the system and the writer frontend goes through this pure PHP based API.

## Usage

The API is provided by a writer [Service](Service.php) object. The system that uses it must provide a context object that implements the [Context](Context.php) interface of this API. 

````
use Edutiek\LongEssayAssessmentService\Writer\Context;
use Edutiek\LongEssayAssessmentService\Writer\Service;

class MyContext implements Context
{
    // implement the context functions
}
````

The writer service will call functions of the context object:
* to get system specific urls
* to set and get access tokens
* to get the settings and instructions of the writing task
* to load already witten texts
* to save written texts
* to set the writing as finished.

Please note that the service class and the context interface of the writer are extensions. Functions that are commonly used by the writer and corrector APIs are defined in a  [BaseService](../Base/BaseService.php) class and [BaseContext](../Base/BaseContext.php) interface. Furthermore, this API uses data objects defined in the [Data](../Data/README.md) directory.  The provided context object should throw a [ContextException](../Exceptions/ContextException.php) in case of errors.

The writer frontend is opened for a certain system _user_ and  _environment_, e.g. a specific essay that has to be written. They are identified by alphanumeric keys which are chosen by the system using the writer. The provided context object must implement an _init()_ function with these keys as parameters and all context functions that store and retrieve data are related to the initialized user and environment.

### Start the Frontend

When the system wants to open the writer frontend for a writing task, the current user and environment are known. The system has to create its context object and initialize it with their keys. Then the service object can be created with this context. 

````
 $context = new MyContext();
 $context->init($user_key, $environment_key);
 
 $service = new Service($context);
 $service->openFrontend();
````

The _openFrontend()_ function of the service will generate a new [ApiToken](../Data/ApiToken.php) and call _setApiToken()_ of the context to store it in the system. Then it redirects to the page given by the context function _getFrontendUrl()_. The service constant FRONTEND_RELATIVE_PATH helps to build that url.

### Handle Frontend Calls

The writer frontend will send REST calls to the backend to read and write data. These calls are sent to an entry script of the system. The url of this script must be provided by the context function _getBackendUrl()_ which is called by the service when the frontend is opened.

The system's entry script will not process the request directly but just initialize the system and then let the service handle the request. At this point in time the user and environment keys are not known by the system because they are hidden in the data of the REST call. Therefore, an uninitialized context object is used to create the service and the service will call the _init()_ function of the context with the keys extracted from the data.

````
 $context = new MyContext();
 $service = new Service($context);
 $service->handleRequest();
````

### Close the Frontend

The frontend is opened in the current window by default, replacing the system page. It provides a button to return to the system. The url is taken from the context function _getReturnUrl()_.