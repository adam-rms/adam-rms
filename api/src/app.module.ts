import { Module } from "@nestjs/common";
import { TypeOrmModule, TypeOrmModuleOptions } from "@nestjs/typeorm";
import { AppController } from "./app.controller";
import { AppService } from "./app.service";
import { ServeStaticModule } from "@nestjs/serve-static";
import { join } from "path";
import typeOrmOptionsInput from "./ormconfig";
const typeOrmOptions: TypeOrmModuleOptions = {
  ...typeOrmOptionsInput,
  migrationsTableName: "migrations_typeorm",
  migrationsRun: true,
  autoLoadEntities: false,
  keepConnectionAlive: true,
  retryAttempts: 10,
  retryDelay: 1000,
  synchronize: false,
};

//import { UsersModule } from "./users/users.module";

@Module({
  imports: [
    TypeOrmModule.forRoot(typeOrmOptions),
    ServeStaticModule.forRoot({
      rootPath: join(__dirname, "..", "static"),
      serveStaticOptions: {
        dotfiles: "ignore",
        index: ["index.html"],
        fallthrough: false,
        lastModified: false,
        redirect: true,
      },
    }),
    //UsersModule,
  ],
  controllers: [AppController],
  providers: [AppService],
})
export class AppModule {}
