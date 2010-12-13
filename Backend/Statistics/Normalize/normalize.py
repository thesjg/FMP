#!/usr/local/bin/python

import ConfigParser
from optparse import OptionParser
import psycopg

class SQLResult:

    Data = []

    def __init__(self, data):
        self.Data = data

    def __getitem__(self, key):
        return self.Data[key]

    def __len__(self):
        return len(self.Data)

class SQL:

    Connection = None
    DB = psycopg

    def __init__(self, database, username, password='',
                 hostname='localhost', port='5432'):

        self.Connection = self.DB.connect(database=database, user=username,
                                          password=password, host=hostname,
                                          port=port);

    def Query(self, query):
        cursor = self.Connection.cursor()

        cursor.setinputsizes(None)
        cursor.execute(query)

        if query[0] == 'S' or query[0] == 's':
            return SQLResult(cursor.fetchall())
        else:
            return None

    def Commit(self):
        self.Connection.commit()

class HandlerConfig(ConfigParser.ConfigParser):

    ConfigFile = None

    Sections = []

    Command = {}

    General = {}
    Video = {}
    Audio = {}
    Image = {}

    def __init__(self, config, defaults):
        self.ConfigFile = config
        self.Sections = ['general', 'hourly', 'daily', 'weekly']

        ConfigParser.ConfigParser.__init__(self)
        self.run()

    def run(self):
        self.read(self.ConfigFile)

        for section in self.Sections:
            if self.has_section(section) == 0:
                raise Exception('Missing configuration section: %s' % section)

def parse_options(r_options):
    usage = 'usage: %prog [options] arg';

    parser = OptionParser(usage);

    parser.add_option('-i', '--interval', dest='interval', type='string', help='')

    (options, args) = parser.parse_args()
    err = False

    if not options.interval:
        print "Interval must be specified."
        err = True

    if err == True:
        print
        parser.print_help()

    r_options['interval'] = options.interval


if __name__ == '__main__':

    # Check that named interval has a configuration section

    options = {}
    parse_options(options)

    Conf = HandlerConfig('normalize.conf')
