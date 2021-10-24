declare const module: any;
import { NestFactory } from "@nestjs/core";
import { AppModule } from "./app.module";
import { SwaggerModule } from "@nestjs/swagger";
import { swaggerCustomOptions, swaggerDocumentBuilder } from "./swagger.config";
const PORT = process.env.PORT || 8080;
async function bootstrap() {
  const app = await NestFactory.create(AppModule);

  const swaggerDocument = SwaggerModule.createDocument(
    app,
    swaggerDocumentBuilder,
  );
  SwaggerModule.setup("docs", app, swaggerDocument, swaggerCustomOptions);

  await app.listen(PORT);
  console.log(`Application is running on: ${await app.getUrl()}`);

  if (module.hot) {
    module.hot.accept();
    module.hot.dispose(() => app.close());
  }
}
bootstrap();
