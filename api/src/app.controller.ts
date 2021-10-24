import { Controller, Get } from "@nestjs/common";
import { AppService } from "./app.service";
import { ApiTags, ApiOkResponse } from "@nestjs/swagger";

@ApiTags("Meta")
@Controller()
export class AppController {
  constructor(private readonly appService: AppService) {}

  @Get("/ping")
  @ApiOkResponse({ description: 'Returns "Pong"' })
  getHello(): string {
    return this.appService.getPong();
  }
}
