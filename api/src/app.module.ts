import { Module } from "@nestjs/common";
import { TypeOrmModule } from "@nestjs/typeorm";
import { AppController } from "./app.controller";
import { AppService } from "./app.service";

//import { UsersModule } from "./users/users.module";

@Module({
  imports: [
    TypeOrmModule.forRoot({
      retryAttempts: 10,
      retryDelay: 1000,
      name: "default",
      type: "mysql",
      host: "localhost",
      port: 3306,
      username: "root",
      password: "",
      database: "adamrms",
      entities: ["dist/**/*.entity{ .ts,.js}"],
      synchronize: false,
      migrations: ["dist/migrations/*{.ts,.js}"],
      migrationsTableName: "migrations_typeorm",
      migrationsRun: true,
      autoLoadEntities: false,
      keepConnectionAlive: true,
    }),
    //UsersModule,
  ],
  controllers: [AppController],
  providers: [AppService],
})
export class AppModule {}
