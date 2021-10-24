let databaseConfig;
if (process.env.DATABASE_URL && process.env.DATABASE_URL.includes("postgres")) {
  /**
   * Heroku Config
   */
  databaseConfig = {
    type: "postgres",
    url: process.env.DATABASE_URL,
  };
} else {
  /**
   * Local Debugging Config
   */
  databaseConfig = {
    type: "mysql",
    host: "localhost",
    port: 3306,
    username: "root",
    password: null,
  };
}

export default {
  ...databaseConfig,
  retryAttempts: 10,
  retryDelay: 1000,
  name: "default",
  database: "adamrms2",
  entities: ["dist/**/*.entity{ .ts,.js}"],
  synchronize: false,
  migrations: ["dist/migrations/*{.ts,.js}"],
  cli: {
    entitiesDir: "src/",
    migrationsDir: "src/migrations",
    subscribersDir: "src/subscribers",
  },
  migrationsTableName: "migrations_typeorm",
  migrationsRun: true,
  autoLoadEntities: false,
  keepConnectionAlive: true,
};
