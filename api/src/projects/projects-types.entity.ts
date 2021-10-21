import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Instances } from "../instances/instances.entity";

@Index("projectsTypes_instances_instances_id_fk", ["instancesId"], {})
@Entity("projectstypes", { schema: "adamrms" })
export class Projectstypes {
  @PrimaryGeneratedColumn({ type: "int", name: "projectsTypes_id" })
  projectsTypesId: number;

  @Column("varchar", { name: "projectsTypes_name", length: 200 })
  projectsTypesName: string;

  @Column("int", { name: "instances_id" })
  instancesId: number;

  @Column("tinyint", {
    name: "projectsTypes_deleted",
    width: 1,
    default: () => "'0'",
  })
  projectsTypesDeleted: boolean;

  @Column("tinyint", {
    name: "projectsTypes_config_finance",
    width: 1,
    default: () => "'1'",
  })
  projectsTypesConfigFinance: boolean;

  @Column("int", { name: "projectsTypes_config_files", default: () => "'1'" })
  projectsTypesConfigFiles: number;

  @Column("int", { name: "projectsTypes_config_assets", default: () => "'1'" })
  projectsTypesConfigAssets: number;

  @Column("int", { name: "projectsTypes_config_client", default: () => "'1'" })
  projectsTypesConfigClient: number;

  @Column("int", { name: "projectsTypes_config_venue", default: () => "'1'" })
  projectsTypesConfigVenue: number;

  @Column("int", { name: "projectsTypes_config_notes", default: () => "'1'" })
  projectsTypesConfigNotes: number;

  @Column("int", { name: "projectsTypes_config_crew", default: () => "'1'" })
  projectsTypesConfigCrew: number;

  @ManyToOne(() => Instances, (instances) => instances.projectstypes, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;
}
