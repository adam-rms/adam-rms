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
  synchronize: false,
  name: "default",
  database: "adamrms",
  entities: ["dist/**/*.entity{ .ts,.js}"],
  migrations: ["dist/migrations/*{.ts,.js}"],
  cli: {
    entitiesDir: "src/",
    migrationsDir: "src/migrations",
    subscribersDir: "src/subscribers",
  },
};
