import re

import pymysql

class WarparsePlugin:
    def __init__(self, bot):
        self.bot = bot

    def startup(self, config):
        self.bot.registerEvent("public_message", self.on_chatmsg)

        self.config = config
        assert "db_host" in config
        assert "db_user" in config
        assert "db_pass" in config
        assert "db_name" in config

        self.conn = pymysql.connect(
            host=self.config["db_host"],
            user=self.config["db_user"],
            passwd=self.config["db_pass"],
            db=self.config["db_name"]
        )

        self.cursor = self.conn.cursor()

        self.cursor.execute("""
            CREATE TABLE IF NOT EXISTS `warparse_logs` (
                `id` INTEGER NULL AUTO_INCREMENT DEFAULT NULL,
                `type` INTEGER NULL DEFAULT NULL,
                `time` INTEGER NULL DEFAULT NULL,
                `user` CHAR(24) NULL DEFAULT NULL,
                `channel` CHAR(24) NULL DEFAULT NULL,
                `network` CHAR(16) NULL DEFAULT NULL,
                `num` INTEGER NULL DEFAULT NULL,
                `info` VARCHAR(128) NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            );
        """)

    def shutdown(self):
        self.cursor.close()
        self.conn.close()

    _CWRE = re.compile("\[CW\] #(.+) @ (.+) - (.+) - Requested a (\d+) vs "
                        "\d+(?: \(Additional info: (.+)\))?");
    _PCWRE = re.compile("\[PCW\] #(.+) @ (.+) - (.+) - Requested a (\d+) vs "
                        "\d+(?: \(Additional info: (.+)\))?");
    _RINGERRE = re.compile("\[RINGER\] #(.+) @ (.+) - (.+) - Requesting (\d+)"
                           "(?: \(Additional info: (.+)\))");

    def on_chatmsg(self, ev):
        nick = ev.source.nick
        message = ev.arguments[0]

        if nick[:6] != "warbot":
            pass

        foundInfo = False

        match = self._CWRE.match(message)
        if match is not None:
            channel = match.group(1)
            network = match.group(2)
            user = match.group(3)
            num = match.group(4)
            info = match.group(5)

            self.bot.say("CW: {} in #{} on {}. Wants {}. {}"
                         .format(user, channel, network, num, info))
            return

        match = self._PCWRE.match(message)
        if match is not None:
            channel = match.group(1)
            network = match.group(2)
            user = match.group(3)
            num = match.group(4)
            info = match.group(5)

            self.bot.say("PCW: {} in #{} on {}. Wants {}. {}"
                         .format(user, channel, network, num, info))
            return

        match = self._RINGERRE.match(message)
        if match is not None:
            channel = match.group(1)
            network = match.group(2)
            user = match.group(3)
            num = match.group(4)
            info = match.group(5)

            self.bot.say("Ringer: {} in #{} on {}. Wants {}. {}"
                         .format(user, channel, network, num, info))
            return


