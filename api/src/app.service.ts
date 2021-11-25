import { Injectable } from "@nestjs/common";

@Injectable()
export class AppService {
  getPong(): string {
    return "Pong";
  }

  getPingu(): string {
    return "NOOT NOOT!";
  }
}
