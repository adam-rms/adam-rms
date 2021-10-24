import { SwaggerCustomOptions } from "@nestjs/swagger";
import { DocumentBuilder } from "@nestjs/swagger";
export const swaggerCustomOptions: SwaggerCustomOptions = {
  swaggerOptions: {
    persistAuthorization: true,
  },
  customSiteTitle: "AdamRMS API Docs",
  customCssUrl: "/swagger/swagger-theme.css",
  customfavIcon: "/swagger/favicon.png",
  explorer: false,
};

const description = `
## Welcome to the REST API Documentation
This page is intended for developers who are interested in building clients that interact with AdamRMS - it's not intended for end users.

This is auto-generated documentation for the REST API, powered by Swagger. You can use this page as a playground with the "Try it out" buttons.
#### Postman Collection
An [Open API Spec](/docs-json) is available, which can be imported into Postman.

### Useful Links

[Github](https://github.com/bstudios/adam-rms)

[Style Guide](https://adam-rms.com/docs/next/developer/style-guide)
`;

export const swaggerDocumentBuilder = new DocumentBuilder()
  .setTitle("AdamRMS")
  .setDescription(description)
  .setVersion("v2")
  .setContact("AdamRMS", "", "")
  .setLicense("AGPLv3", "https://www.gnu.org/licenses/agpl-3.0.html")
  .setExternalDoc(
    "Developer Guide",
    "https://adam-rms.com/docs/next/developer/intro",
  )
  .setTermsOfService("https://adam-rms.com/legal")
  .addTag("Meta", "Meta about the api")
  .build();
