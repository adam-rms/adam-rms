import { Column, Entity, PrimaryGeneratedColumn } from "typeorm";

@Entity("loginattempts", { schema: "adamrms" })
export class Loginattempts {
  @PrimaryGeneratedColumn({ type: "int", name: "loginAttempts_id" })
  loginAttemptsId: number;

  @Column("timestamp", {
    name: "loginAttempts_timestamp",
    default: () => "CURRENT_TIMESTAMP",
  })
  loginAttemptsTimestamp: Date;

  @Column("varchar", { name: "loginAttempts_textEntered", length: 500 })
  loginAttemptsTextEntered: string;

  @Column("varchar", { name: "loginAttempts_ip", nullable: true, length: 500 })
  loginAttemptsIp: string | null;

  @Column("tinyint", { name: "loginAttempts_blocked", width: 1 })
  loginAttemptsBlocked: boolean;

  @Column("tinyint", { name: "loginAttempts_successful", width: 1 })
  loginAttemptsSuccessful: boolean;
}
