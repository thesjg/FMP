#!/usr/local/bin/python

from boto.s3.connection import S3Connection
from boto.s3.key import Key
from optparse import OptionParser
from ConfigParser import ConfigParser
import sys


class HandlerOptions(OptionParser):

    commands = ['bucket-create', 'bucket-delete',
                'media-store', 'media-delete',
                'list-buckets', 'list-media',
                'log-retrieve']
    
    def __init__(self, r_options):
        usage = 'usage: %prog [options]'
        OptionParser.__init__(self, usage)

        self.add_option('-c', '--command', dest='command', type='string', help='')
        self.add_option('-b', '--bucket', dest='bucket', type='string', help='')
        self.add_option('-f', '--file', dest='file', type='string', help='')
        self.add_option('-k', '--key', dest='key', type='string', help='')
        self.add_option('-t', '--token', dest='token', type='string', help='')

        (options, args) = self.parse_args()
        err = False

        if not options.command or options.command not in self.commands:
            print "Valid command must be specified:", self.commands
            err = True

        if options.command == 'media-store' or \
            options.command == 'log-retrieve':

            if not options.file:
                print "Filename must be specified with the selected command"
                err = True

        if options.command == 'media-store' or \
            options.command == 'media-delete':
            
            if not options.key:
                print "Key must be specified with the selected command"
                err = True

        if not options.bucket and not options.command == 'list-buckets':
            print "Bucket must be specified"
            err = True

        if err == True:
            print
            self.print_help()
            sys.exit(1)

        r_options['command'] = options.command
        r_options['bucket'] = options.bucket
        
        if options.file:
            r_options['file'] = options.file
        
        if options.key:
            r_options['key'] = options.key

        if options.token:
            r_options['token'] = options.token


class HandlerConfig(ConfigParser):

    configFile = None
    sections = []

    general = {}

    def __init__(self, config):
        self.configFile = config
        self.sections = ['general']

        ConfigParser.__init__(self)
        self.run()

    def run(self):
        self.read(self.configFile)

        for section in self.sections:
            if self.has_section(section) == 0:
                raise Exception('Missing configuration section: %s' % section)

        self.general['access_key_id'] = self.get('general', 'access_key_id')
        self.general['secret_access_key'] = self.get('general', 'secret_access_key')
        self.general['product_token'] = self.get('general', 'product_token')


class FxS3(object):

    headers = {}

    def __init__(self, config, options):
        self.config = config
        self.options = options

        if self.options.has_key('token'):
            self.headers['x-amz-security-token'] = "%s,%s" % (self.conf.general['product_token'],
                                                              self.options['token'])

        self.connect()
        self.run()

    def connect(self):
        self.connection = S3Connection(self.config.general['access_key_id'],
                                       self.config.general['secret_access_key'])

    def createBucket(self):
        try:
            b = self.connection.create_bucket(self.options['bucket'], self.headers)
            b.set_acl('public-read')
            return b
        except:
            return False
        
    def deleteBucket(self):
        try:
            self.connection.delete_bucket(self.options['bucket'])
            return True
        except:
            return False

    def getBucket(self):
        try:
            try:
                return self.connection.get_bucket(self.options['bucket'])
            except:
                try:
                    return self.createBucket()
                except:
                    return False
        except:
            return False

    def uploadFile(self):
        try:
            b = self.getBucket()
            k = Key(b)
            k.key = self.options['key']
            k.set_contents_from_filename(self.options['file'], self.headers)
            k.set_acl('public-read')
            return True
        except:
            return False

    def deleteFile(self):
        try:
            b = self.getBucket()
            b.delete_key(self.options['key'])
            return True
        except:
            return False

    def listBuckets(self):
        try:
            return self.connection.get_all_buckets()
        except:
            return False

    def listBucket(self):
        try:
            b = self.getBucket()
            return b.get_all_keys(self.headers)
        except:
            return False

    def getLogs(self):
        pass

    def run(self):
        if self.options['command'] == 'bucket-create':
            if self.createBucket():
                print "Successfully created bucket:", self.options['bucket']
                sys.exit(0)
            else:
                print "Unable to create bucket:", self.options['bucket']
                sys.exit(1)
        
        elif self.options['command'] == 'bucket-delete':
            if self.deleteBucket():
                print "Successfully deleted bucket:", self.options['bucket']
                sys.exit(0)
            else:
                print "Unable to delete bucket:", self.options['bucket']
                sys.exit(1)
                
        elif self.options['command'] == 'media-store':
            if self.uploadFile():
                print "Successfully stored media:", self.options['key']
                sys.exit(0)
            else:
                print "Unable to store media:", self.options['key']
                sys.exit(1)

        elif self.options['command'] == 'media-delete':
            if self.deleteFile():
                print "Successfully deleted media:", self.options['key']
                sys.exit(0)
            else:
                print "Unable to delete media:", self.options['key']
                sys.exit(1)

        elif self.options['command'] == 'list-buckets':
            buckets = self.listBuckets()
            if buckets:
                for bucket in buckets:
                    print bucket.name

                exit(0)
            else:
                print "Unable to list buckets"
                exit(1)

        elif self.options['command'] == 'list-media':
            medias = self.listBucket()
            if medias:
                for media in medias:
                    print media.name

                exit(0)
            else:
                print "Unable to list media"
                exit(1)


if __name__ == "__main__":

    conf = HandlerConfig('/z/www/upload.flixn.evilprojects.net/FxS3/fxs3.conf')

    options = {}
    HandlerOptions(options)

    FxS3(conf, options)