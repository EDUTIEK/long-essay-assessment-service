# LongEssayAssessmentService

This service can be integrated by PHP based systems to use two javascript applications which provide user interfaces 
for the writing and correction of long text-based exams:
* [long-essay-writer](https://github.com/EDUTIEK/long-essay-writer) 
* [long-essay corrector](https://github.com/EDUTIEK/long-essay-corrector)  

The service has no own status. It provides API functions:

* [Writer](./src/Writer/README.md) includes the service functions to user the writer.
* [Corrector](./src/Corrector/README.md) includes the service functions to user the corrector.
* [Data](./src/Data/README.md) defines data objects for the use of the writer and corrector APIs.

### Usage

Add the following dependency to your application:

````
{
    "repositories": [
        {
        "type": "vcs",
        "url": "https://github.com/EDUTIEK/LongEssayAssessmentService"
        }
    ],
    "require": {
        "edutiek/long-essay-service": "dev-main"
    },

````
Then call ``composer install --no-dev`` to install it.

The built distribution files of the javascript applications are already included as node modules with packed sources 
when the service is installed. These node modules are included in the git repository of the service and are updated
with the services.

If you need to get the latest versions of the these apps, you may call their update separately. But note that this
may cause incompatible calls.


### Update the writer
```
npm install long-essay-writer
```

### Update the writer
```
npm install long-essay-corrector
```
