import { Column, Entity, PrimaryGeneratedColumn } from "typeorm";

@Entity("positionsgroups", { schema: "adamrms" })
export class Positionsgroups {
  @PrimaryGeneratedColumn({ type: "int", name: "positionsGroups_id" })
  positionsGroupsId: number;

  @Column("varchar", { name: "positionsGroups_name", length: 255 })
  positionsGroupsName: string;

  @Column("varchar", {
    name: "positionsGroups_actions",
    nullable: true,
    length: 1000,
  })
  positionsGroupsActions: string | null;
}
