import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Users } from "../auth/users/users.entity";
import { Modulessteps } from "./modules-steps.entity";
import { Modules } from "./modules.entity";

@Index("userModules_modules_modules_id_fk", ["modulesId"], {})
@Index("userModules_users_users_userid_fk", ["usersUserid"], {})
@Index(
  "userModules_modulesSteps_modulesSteps_id_fk",
  ["userModulesCurrentStep"],
  {},
)
@Entity("usermodules", { schema: "adamrms" })
export class Usermodules {
  @PrimaryGeneratedColumn({ type: "int", name: "userModules_id" })
  userModulesId: number;

  @Column("int", { name: "modules_id" })
  modulesId: number;

  @Column("int", { name: "users_userid" })
  usersUserid: number;

  @Column("varchar", {
    name: "userModules_stepsCompleted",
    nullable: true,
    length: 1000,
  })
  userModulesStepsCompleted: string | null;

  @Column("int", { name: "userModules_currentStep", nullable: true })
  userModulesCurrentStep: number | null;

  @Column("timestamp", { name: "userModules_started" })
  userModulesStarted: Date;

  @Column("timestamp", { name: "userModules_updated" })
  userModulesUpdated: Date;

  @ManyToOne(() => Modulessteps, (modulessteps) => modulessteps.usermodules, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([
    { name: "userModules_currentStep", referencedColumnName: "modulesStepsId" },
  ])
  userModulesCurrentStep2: Modulessteps;

  @ManyToOne(() => Modules, (modules) => modules.usermodules, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "modules_id", referencedColumnName: "modulesId" }])
  modules: Modules;

  @ManyToOne(() => Users, (users) => users.usermodules, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "users_userid", referencedColumnName: "usersUserid" }])
  usersUser: Users;
}
