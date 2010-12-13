#!/usr/local/bin/python

import Queue
import threading
import sys
import time
import ConfigParser
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

class HandlerInput:
    def __init__(self):
        pass

    def run(self):
        overflow = []

        while 1:
            line = sys.stdin.readline()
            if line == '':
                break

            try:
                QueueInput.put(line, False);
            except Queue.Full:
                overflow.append(line)

            if len(overflow) > 0:
                while 1:
                    try:
                        line = overflow.pop()
                        QueueInput.put(line, False)
                    except Queue.Full:
                        overflow.append(line)
                        break

class HandlerInputParser:
    def __init__(self):
        pass

    def run(self):
        while 1:
            line = QueueInput.get()
            QueueLogger.put(line)

            line_arr = line.split(' ')[6:10]
            file = line_arr[0]
            size = line_arr[-1]

            file = file.split('/')[4]
            file, ext = file.split('.')

            DictBWLogLock.acquire()

            if DictBWLog.has_key(file):
                DictBWLog[file] = [int(size) + DictBWLog[file][0], ext, int(time.time())]
            else:
                DictBWLog[file] = [int(size), ext, int(time.time())]

            DictBWLogLock.release();

class HandlerTimers:
    def __init__(self):
        self.SleepTime = Conf.General['interval']

    def run(self):
        time.sleep(self.SleepTime)

        while 1:
            start = int(time.time())
            LocalDictBWLog = {}

            DictBWLogLock.acquire();

            for key in DictBWLog.keys():
                if DictBWLog[key][2] < start - self.SleepTime:
                    LocalDictBWLog[key] = DictBWLog.get(key)
                    del(DictBWLog[key])

            DictBWLogLock.release();

            QueueOutput.put(LocalDictBWLog)

            time.sleep(self.SleepTime - (int(time.time()) - start))

class HandlerOutput:

    SQL = None

    def __init__(self):
        try:
            self.SQL = SQL(Conf.Database['database'], Conf.Database['username'],
                           Conf.Database['password'], Conf.Database['hostname'])
        except:
            pass

    def run(self):
        while 1:
            LocalDictBWLog = QueueOutput.get()

            print LocalDictBWLog

            query = 'INSERT INTO usage_bandwidth (video_id, bandwidth) VALUES (%d, %d)';
            for key in LocalDictBWLog.keys():
                print query % (int(self.convert(key)), LocalDictBWLog[key][0])

    def convert(self, n, newbase=10, oldbase=36):
        INT_TO_DIGIT = [ x for x in "0123456789abcdefghijklmnopqrstuvwxyz"
                         "_ABCDEFGHIJKLMNOPQRSTUVWXYZ~!'()*+"]
        DIGIT_TO_INT = dict([ (y, x) for x, y in enumerate(INT_TO_DIGIT) ])

        nums = [ DIGIT_TO_INT[x] for x in str(n) ]
        nums.reverse()

        r = 1
        total = 0
        for x in nums:
            total += r * x
            r *= oldbase

        if total == 0:
            return '0'

        converted = []
        while total:
            total, remainder = divmod(total, newbase)
            converted.append(INT_TO_DIGIT[remainder])

        converted.reverse()
        return ''.join(converted)


class HandlerLogger:
    def __init__(self):
        pass

    def run(self):
        while 1:
            print QueueLogger.get(),

class HandlerConfig(ConfigParser.ConfigParser):

    ConfigFile = None

    Sections = []

    General = {}
    Database = {}
    Log = {}

    def __init__(self, config):
        self.ConfigFile = config
        self.Sections = ['general', 'database', 'log']

        ConfigParser.ConfigParser.__init__(self)
        self.run()

    def run(self):
        self.read(self.ConfigFile)

        for section in self.Sections:
            if self.has_section(section) == 0:
                raise Exception('Missing configuration section: %s' % section)

        self.General['interval'] = int(self.get('general', 'interval'))

        self.Database['hostname'] = self.get('database', 'hostname')
        self.Database['username'] = self.get('database', 'username')
        self.Database['password'] = self.get('database', 'password')
        self.Database['database'] = self.get('database', 'database')


class ThreadWrapperInput(threading.Thread):
    def run(self):
        handler = HandlerInput()
        handler.run()

class ThreadWrapperInputParser(threading.Thread):
    def run(self):
        handler = HandlerInputParser()
        handler.run()

class ThreadWrapperTimers(threading.Thread):
    def run(self):
        handler = HandlerTimers()
        handler.run()

class ThreadWrapperOutput(threading.Thread):
    def run(self):
        handler = HandlerOutput()
        handler.run()

class ThreadWrapperLogger(threading.Thread):
    def run(self):
        handler = HandlerLogger()
        handler.run()

if __name__ == '__main__':

    Conf = HandlerConfig('webmonitor.conf')

    DictBWLog = {}
    DictBWLogLock = threading.Lock()

    QueueInput = Queue.Queue(0)
    QueueOutput = Queue.Queue(0)
    QueueLogger = Queue.Queue(0)

    ThreadWrapperInput().start()
    ThreadWrapperInputParser().start()
    ThreadWrapperTimers().start()
    ThreadWrapperOutput().start()
    ThreadWrapperLogger().start()