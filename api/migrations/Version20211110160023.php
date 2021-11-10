<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211110160023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE greeting_id_seq CASCADE');
        $this->addSql('CREATE TABLE app_user (id UUID NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, spotify_id VARCHAR(255) DEFAULT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88BDF3E9E7927C74 ON app_user (email)');
        $this->addSql('COMMENT ON COLUMN app_user.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN app_user.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN app_user.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE match_up (id UUID NOT NULL, score BIGINT DEFAULT NULL, distance DOUBLE PRECISION DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_active BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN match_up.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN match_up.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN match_up.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE match_up_flags (id UUID NOT NULL, match_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, calcul_flag BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_807F46A52ABEACD6 ON match_up_flags (match_id)');
        $this->addSql('COMMENT ON COLUMN match_up_flags.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN match_up_flags.match_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN match_up_flags.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN match_up_flags.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE message (id UUID NOT NULL, author_id UUID DEFAULT NULL, talk_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, content TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6BD307FF675F31B ON message (author_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307F6F0601D5 ON message (talk_id)');
        $this->addSql('COMMENT ON COLUMN message.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN message.author_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN message.talk_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN message.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN message.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE message_user (id UUID NOT NULL, message_id UUID NOT NULL, participant_id UUID NOT NULL, reading_status BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_24064D90537A1329 ON message_user (message_id)');
        $this->addSql('CREATE INDEX IDX_24064D909D1C3019 ON message_user (participant_id)');
        $this->addSql('COMMENT ON COLUMN message_user.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN message_user.message_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN message_user.participant_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN message_user.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN message_user.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE profile_theme (id UUID NOT NULL, name VARCHAR(100) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN profile_theme.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN profile_theme.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN profile_theme.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE talk (id UUID NOT NULL, match_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, upadted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F24D5BB2ABEACD6 ON talk (match_id)');
        $this->addSql('COMMENT ON COLUMN talk.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN talk.match_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN talk.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN talk.upadted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE talk_user (id UUID NOT NULL, talk_id UUID NOT NULL, participant_id UUID NOT NULL, reading_status BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6FB6AE156F0601D5 ON talk_user (talk_id)');
        $this->addSql('CREATE INDEX IDX_6FB6AE159D1C3019 ON talk_user (participant_id)');
        $this->addSql('COMMENT ON COLUMN talk_user.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN talk_user.talk_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN talk_user.participant_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN talk_user.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN talk_user.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE track (id UUID NOT NULL, name VARCHAR(255) NOT NULL, artist VARCHAR(255) NOT NULL, album VARCHAR(255) NOT NULL, picture_url TEXT DEFAULT NULL, popularity BIGINT DEFAULT NULL, spotify_id VARCHAR(255) DEFAULT NULL, deezer_id VARCHAR(255) DEFAULT NULL, spotify_preview_url TEXT DEFAULT NULL, deezer_preview_url TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN track.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN track.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN track.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE user_data (id UUID NOT NULL, uploaded_picture_id UUID DEFAULT NULL, user_id UUID DEFAULT NULL, name VARCHAR(64) NOT NULL, birth_date DATE DEFAULT NULL, location geography(GEOMETRY, 4326) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, streaming_platform VARCHAR(255) CHECK(streaming_platform IN (\'spotify\', \'deezer\')) DEFAULT NULL, gender VARCHAR(255) CHECK(gender IN (\'male\', \'female\')) DEFAULT NULL, sexual_orientation VARCHAR(255) CHECK(sexual_orientation IN (\'male\', \'female\', \'both\')) DEFAULT NULL, picture_url TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, activation_token VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D772BFAAB4C7F861 ON user_data (uploaded_picture_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D772BFAAA76ED395 ON user_data (user_id)');
        $this->addSql('COMMENT ON COLUMN user_data.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_data.uploaded_picture_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_data.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_data.streaming_platform IS \'(DC2Type:StreamingPlatformType)\'');
        $this->addSql('COMMENT ON COLUMN user_data.gender IS \'(DC2Type:GenderType)\'');
        $this->addSql('COMMENT ON COLUMN user_data.sexual_orientation IS \'(DC2Type:SexualOrientationType)\'');
        $this->addSql('COMMENT ON COLUMN user_data.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_data.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE user_data_flags (id UUID NOT NULL, user_data_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, geo_flag BOOLEAN DEFAULT NULL, gender_flag BOOLEAN DEFAULT NULL, orientation_flag BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E2DD4BC96FF8BF36 ON user_data_flags (user_data_id)');
        $this->addSql('COMMENT ON COLUMN user_data_flags.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_data_flags.user_data_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_data_flags.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_data_flags.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE user_has_matchup (id UUID NOT NULL, user_id UUID DEFAULT NULL, match_id UUID DEFAULT NULL, is_favorite BOOLEAN DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1B07F71FA76ED395 ON user_has_matchup (user_id)');
        $this->addSql('CREATE INDEX IDX_1B07F71F2ABEACD6 ON user_has_matchup (match_id)');
        $this->addSql('COMMENT ON COLUMN user_has_matchup.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_has_matchup.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_has_matchup.match_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_has_matchup.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_has_matchup.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE user_has_track (id UUID NOT NULL, user_id UUID DEFAULT NULL, track_id UUID DEFAULT NULL, is_super_track BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2D8A65FCA76ED395 ON user_has_track (user_id)');
        $this->addSql('CREATE INDEX IDX_2D8A65FC5ED23C43 ON user_has_track (track_id)');
        $this->addSql('COMMENT ON COLUMN user_has_track.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_has_track.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_has_track.track_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_has_track.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_has_track.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE user_picture (id UUID NOT NULL, file_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN user_picture.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE user_profile (id UUID NOT NULL, track_id UUID DEFAULT NULL, theme_id UUID DEFAULT NULL, user_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D95AB4055ED23C43 ON user_profile (track_id)');
        $this->addSql('CREATE INDEX IDX_D95AB40559027487 ON user_profile (theme_id)');
        $this->addSql('CREATE INDEX IDX_D95AB405A76ED395 ON user_profile (user_id)');
        $this->addSql('COMMENT ON COLUMN user_profile.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_profile.track_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_profile.theme_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_profile.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_profile.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_profile.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE user_track_flags (id UUID NOT NULL, user_track_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, create_flag BOOLEAN DEFAULT NULL, update_flag BOOLEAN DEFAULT NULL, delete_flag BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D2E92A88BE5FD843 ON user_track_flags (user_track_id)');
        $this->addSql('COMMENT ON COLUMN user_track_flags.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_track_flags.user_track_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_track_flags.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_track_flags.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE match_up_flags ADD CONSTRAINT FK_807F46A52ABEACD6 FOREIGN KEY (match_id) REFERENCES match_up (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF675F31B FOREIGN KEY (author_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F6F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message_user ADD CONSTRAINT FK_24064D90537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message_user ADD CONSTRAINT FK_24064D909D1C3019 FOREIGN KEY (participant_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE talk ADD CONSTRAINT FK_9F24D5BB2ABEACD6 FOREIGN KEY (match_id) REFERENCES match_up (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE talk_user ADD CONSTRAINT FK_6FB6AE156F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE talk_user ADD CONSTRAINT FK_6FB6AE159D1C3019 FOREIGN KEY (participant_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_data ADD CONSTRAINT FK_D772BFAAB4C7F861 FOREIGN KEY (uploaded_picture_id) REFERENCES user_picture (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_data ADD CONSTRAINT FK_D772BFAAA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_data_flags ADD CONSTRAINT FK_E2DD4BC96FF8BF36 FOREIGN KEY (user_data_id) REFERENCES user_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_has_matchup ADD CONSTRAINT FK_1B07F71FA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_has_matchup ADD CONSTRAINT FK_1B07F71F2ABEACD6 FOREIGN KEY (match_id) REFERENCES match_up (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_has_track ADD CONSTRAINT FK_2D8A65FCA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_has_track ADD CONSTRAINT FK_2D8A65FC5ED23C43 FOREIGN KEY (track_id) REFERENCES track (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB4055ED23C43 FOREIGN KEY (track_id) REFERENCES track (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB40559027487 FOREIGN KEY (theme_id) REFERENCES profile_theme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_track_flags ADD CONSTRAINT FK_D2E92A88BE5FD843 FOREIGN KEY (user_track_id) REFERENCES user_has_track (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE greeting');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307FF675F31B');
        $this->addSql('ALTER TABLE message_user DROP CONSTRAINT FK_24064D909D1C3019');
        $this->addSql('ALTER TABLE talk_user DROP CONSTRAINT FK_6FB6AE159D1C3019');
        $this->addSql('ALTER TABLE user_data DROP CONSTRAINT FK_D772BFAAA76ED395');
        $this->addSql('ALTER TABLE user_has_matchup DROP CONSTRAINT FK_1B07F71FA76ED395');
        $this->addSql('ALTER TABLE user_has_track DROP CONSTRAINT FK_2D8A65FCA76ED395');
        $this->addSql('ALTER TABLE user_profile DROP CONSTRAINT FK_D95AB405A76ED395');
        $this->addSql('ALTER TABLE match_up_flags DROP CONSTRAINT FK_807F46A52ABEACD6');
        $this->addSql('ALTER TABLE talk DROP CONSTRAINT FK_9F24D5BB2ABEACD6');
        $this->addSql('ALTER TABLE user_has_matchup DROP CONSTRAINT FK_1B07F71F2ABEACD6');
        $this->addSql('ALTER TABLE message_user DROP CONSTRAINT FK_24064D90537A1329');
        $this->addSql('ALTER TABLE user_profile DROP CONSTRAINT FK_D95AB40559027487');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F6F0601D5');
        $this->addSql('ALTER TABLE talk_user DROP CONSTRAINT FK_6FB6AE156F0601D5');
        $this->addSql('ALTER TABLE user_has_track DROP CONSTRAINT FK_2D8A65FC5ED23C43');
        $this->addSql('ALTER TABLE user_profile DROP CONSTRAINT FK_D95AB4055ED23C43');
        $this->addSql('ALTER TABLE user_data_flags DROP CONSTRAINT FK_E2DD4BC96FF8BF36');
        $this->addSql('ALTER TABLE user_track_flags DROP CONSTRAINT FK_D2E92A88BE5FD843');
        $this->addSql('ALTER TABLE user_data DROP CONSTRAINT FK_D772BFAAB4C7F861');
        $this->addSql('CREATE SEQUENCE greeting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE greeting (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE match_up');
        $this->addSql('DROP TABLE match_up_flags');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE message_user');
        $this->addSql('DROP TABLE profile_theme');
        $this->addSql('DROP TABLE talk');
        $this->addSql('DROP TABLE talk_user');
        $this->addSql('DROP TABLE track');
        $this->addSql('DROP TABLE user_data');
        $this->addSql('DROP TABLE user_data_flags');
        $this->addSql('DROP TABLE user_has_matchup');
        $this->addSql('DROP TABLE user_has_track');
        $this->addSql('DROP TABLE user_picture');
        $this->addSql('DROP TABLE user_profile');
        $this->addSql('DROP TABLE user_track_flags');
    }
}
