import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Modules } from "./modules.entity";
import { Usermodules } from "./user-modules.entity";

@Index("modulesSteps_modules_modules_id_fk", ["modulesId"], {})
@Entity("modulessteps", { schema: "adamrms" })
export class Modulessteps {
  @PrimaryGeneratedColumn({ type: "int", name: "modulesSteps_id" })
  modulesStepsId: number;

  @Column("int", { name: "modules_id" })
  modulesId: number;

  @Column("tinyint", {
    name: "modulesSteps_deleted",
    width: 1,
    default: () => "'0'",
  })
  modulesStepsDeleted: boolean;

  @Column("tinyint", {
    name: "modulesSteps_show",
    width: 1,
    default: () => "'1'",
  })
  modulesStepsShow: boolean;

  @Column("varchar", { name: "modulesSteps_name", length: 500 })
  modulesStepsName: string;

  @Column("tinyint", { name: "modulesSteps_type", width: 1 })
  modulesStepsType: boolean;

  @Column("longtext", { name: "modulesSteps_content", nullable: true })
  modulesStepsContent: string | null;

  @Column("int", {
    name: "modulesSteps_completionTime",
    nullable: true,
    default: () => "'0'",
  })
  modulesStepsCompletionTime: number | null;

  @Column("longtext", { name: "modulesSteps_internalNotes", nullable: true })
  modulesStepsInternalNotes: string | null;

  @Column("int", { name: "modulesSteps_order", default: () => "'999'" })
  modulesStepsOrder: number;

  @Column("tinyint", {
    name: "modulesSteps_locked",
    comment: "When set this is a like system level step that can't be edited",
    width: 1,
    default: () => "'0'",
  })
  modulesStepsLocked: boolean;

  @ManyToOne(() => Modules, (modules) => modules.modulessteps, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "modules_id", referencedColumnName: "modulesId" }])
  modules: Modules;

  @OneToMany(
    () => Usermodules,
    (usermodules) => usermodules.userModulesCurrentStep2,
  )
  usermodules: Usermodules[];
}
