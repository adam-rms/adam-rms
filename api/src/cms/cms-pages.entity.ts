import {
  Column,
  Entity,
  Index,
  JoinColumn,
  ManyToOne,
  OneToMany,
  PrimaryGeneratedColumn,
} from "typeorm";
import { Instances } from "../instances/instances.entity";
import { Cmspagesdrafts } from "./cms-pages-drafts.entity";
import { Cmspagesviews } from "./cms-pages-views.entity";

@Index("cmsPages_instances_instances_id_fk", ["instancesId"], {})
@Index("cmsPages_cmsPages_cmsPages_id_fk", ["cmsPagesSubOf"], {})
@Entity("cmspages", { schema: "adamrms" })
export class Cmspages {
  @PrimaryGeneratedColumn({ type: "int", name: "cmsPages_id" })
  cmsPagesId: number;

  @Column("int", { name: "instances_id" })
  instancesId: number;

  @Column("tinyint", {
    name: "cmsPages_showNav",
    width: 1,
    default: () => "'0'",
  })
  cmsPagesShowNav: boolean;

  @Column("tinyint", {
    name: "cmsPages_showPublic",
    width: 1,
    default: () => "'0'",
  })
  cmsPagesShowPublic: boolean;

  @Column("tinyint", {
    name: "cmsPages_showPublicNav",
    width: 1,
    default: () => "'1'",
  })
  cmsPagesShowPublicNav: boolean;

  @Column("varchar", {
    name: "cmsPages_visibleToGroups",
    nullable: true,
    length: 1000,
  })
  cmsPagesVisibleToGroups: string | null;

  @Column("int", { name: "cmsPages_navOrder", default: () => "'999'" })
  cmsPagesNavOrder: number;

  @Column("varchar", {
    name: "cmsPages_fontAwesome",
    nullable: true,
    length: 500,
  })
  cmsPagesFontAwesome: string | null;

  @Column("varchar", { name: "cmsPages_name", length: 500 })
  cmsPagesName: string;

  @Column("text", { name: "cmsPages_description", nullable: true })
  cmsPagesDescription: string | null;

  @Column("tinyint", {
    name: "cmsPages_archived",
    width: 1,
    default: () => "'0'",
  })
  cmsPagesArchived: boolean;

  @Column("tinyint", {
    name: "cmsPages_deleted",
    width: 1,
    default: () => "'0'",
  })
  cmsPagesDeleted: boolean;

  @Column("timestamp", {
    name: "cmsPages_added",
    default: () => "CURRENT_TIMESTAMP",
  })
  cmsPagesAdded: Date;

  @Column("int", { name: "cmsPages_subOf", nullable: true })
  cmsPagesSubOf: number | null;

  @ManyToOne(() => Cmspages, (cmspages) => cmspages.cmspages, {
    onDelete: "SET NULL",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "cmsPages_subOf", referencedColumnName: "cmsPagesId" }])
  cmsPagesSubOf2: Cmspages;

  @OneToMany(() => Cmspages, (cmspages) => cmspages.cmsPagesSubOf2)
  cmspages: Cmspages[];

  @ManyToOne(() => Instances, (instances) => instances.cmspages, {
    onDelete: "CASCADE",
    onUpdate: "CASCADE",
  })
  @JoinColumn([{ name: "instances_id", referencedColumnName: "instancesId" }])
  instances: Instances;

  @OneToMany(() => Cmspagesdrafts, (cmspagesdrafts) => cmspagesdrafts.cmsPages)
  cmspagesdrafts: Cmspagesdrafts[];

  @OneToMany(() => Cmspagesviews, (cmspagesviews) => cmspagesviews.cmsPages)
  cmspagesviews: Cmspagesviews[];
}
