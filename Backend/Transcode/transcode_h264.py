#!/usr/local/bin/python

import subprocess
import logging
import ConfigParser
from optparse import OptionParser
import sys
import math


def get_dims(video):

    executable = '/z/www/upload.flixn.evilprojects.net/mediainfo/mediainfo.py'
    command = '%s -f %s' % (executable, video)

    output = subprocess.Popen(command, shell=True, stdout=subprocess.PIPE).communicate()[0]

    width = 0
    height = 0

    for line in output.splitlines():
        if line == 'video':
            continue

        key, value = line.split(': ')

        if key == 'video_width':
            width = int(value)
        elif key == 'video_height':
            height = int(value)
        else:
            continue

    return [width, height]


def get_pads(source_width, source_height, target_width, target_height):

    source_width, source_height = float(source_width), float(source_height)
    target_width, target_height = float(target_width), float(target_height)

    print source_width, source_height, target_width, target_height

    source_ratio = source_width / source_height
    target_ratio = target_width / target_height


    # left, right, top, bottom
    pads = [0, 0, 0, 0]

    try:
        if source_ratio < target_ratio:
            source_width = source_width * (target_height / source_height)
            source_height = target_height

            if source_height < target_height:
                pad_value = (target_height - source_height) / 2
                pads[2], pads[3] = int(math.floor(pad_value)), int(math.ceil(pad_value))
            else:
                pad_value = (target_width - source_width) / 2
                pads[0], pads[1] = int(math.floor(pad_value)), int(math.ceil(pad_value))
        else:
            source_height = source_height * (target_width / source_width)
            source_width = target_width

            if source_width < target_width:
                pad_value = (target_width - source_width) / 2
                pads[0], pads[1] = int(math.floor(pad_value)), int(math.ceil(pad_value))
            else:
                pad_value = (target_height - source_height) / 2
                pads[2], pads[3] = int(math.floor(pad_value)), int(math.ceil(pad_value))

        if pads[0] != 0 or pads[1] != 0:
            if math.fmod(pads[0], 2) != 0.0:
                pads[0] = pads[0] + 1
            if math.fmod(pads[1], 2) != 0.0:
                pads[1] = pads[1] - 1
        elif pads[3] != 0 or pads[4] != 0:
            if math.fmod(pads[2], 2) != 0.0:
                pads[2] = pads[2] + 1
            if math.fmod(pads[3], 2) != 0.0:
                pads[3] = pads[3] - 1
    except:
        pass

    return pads


# XXX
# Fill this out at some point
# It should handle figuring out if we need to maintain aspect ratio's, doing
# all of the requisite math's, etc.
class Transcode:
    def __init__(self):
        pass


class HandlerConfig(ConfigParser.ConfigParser):

    ConfigFile = None

    Sections = []

    Defaults = {}

    General = {}
    Base = {}
    Pass1 = {}
    Pass2 = {}
    Configurable = {}

    # TODO
    Log = {}

    def __init__(self, config, defaults):
        self.ConfigFile = config
        self.Sections = ['general', 'base', 'pass1', 'pass2', 'configurable']

        self.Defaults = defaults

        ConfigParser.ConfigParser.__init__(self)
        self.run()

    def run(self):
        self.read(self.ConfigFile)

        for section in self.Sections:
            if self.has_section(section) == 0:
                raise Exception('Missing configuration section: %s' % section)

        if self.get('general', 'multipass') == 'T':
            self.General['multipass'] = True
            
        if self.get('general', 'threaded') == 'T':
            self.General['threaded'] = True

        self.General['command'] = self.get('general', 'command')
        self.General['order'] = self.get('general', 'order')

        for option in self.items('base'):
            self.Base[option[0]] = option[1]
            
        if self.General['multipass'] == True:
            for option in self.items('pass1'):
                self.Pass1[option[0]] = option[1]
            for option in self.items('pass2'):
                self.Pass2[option[0]] = option[1]
        else:
            self.Pass1 = None
            self.Pass2 = None

        if self.Defaults['audioBitrate'] == '0':
            self.Defaults['audioCodec'] = 'copy'
        else:
            self.Defaults['audioCodec'] = 'libfaac';

        swidth, sheight = get_dims(self.Defaults['inputFile'])
        (self.Defaults['padLeft'],
         self.Defaults['padRight'],
         self.Defaults['padTop'],
         self.Defaults['padBottom']) = get_pads(swidth, sheight,
                                                self.Defaults['videoWidth'],
                                                self.Defaults['videoHeight']);

        self.Defaults['videoWidth'] = self.Defaults['videoWidth'] - \
                                      (self.Defaults['padLeft'] + self.Defaults['padRight'])

        self.Defaults['videoHeight'] = self.Defaults['videoHeight'] - \
                                      (self.Defaults['padTop'] + self.Defaults['padBottom'])

        for option in self.items('configurable'):
            option_t = option[1]
            if option[0] == 'r' and self.Defaults['videoFramerate'] == '0':
                continue

            for default in self.Defaults:
                option_t = option_t.replace('${%s}' % default, str(self.Defaults[default]))

            self.Configurable[option[0]] = option_t


# This could be 50% its current size, rewrite
# Subclass OptionParser
# Check that width and height are divisible by 16 or 8(warn)
# 
def parse_options(r_options):
    usage = 'usage: %prog [options] arg';
    
    parser = OptionParser(usage);
    
    parser.add_option('-o', '--output', dest='outputFile', type='string', help='')
    parser.add_option('-i', '--input', dest='inputFile', type='string', help='')
    parser.add_option('-x', '--width', dest='videoWidth', type='int', help='')
    parser.add_option('-y', '--height', dest='videoHeight', type='int', help='')
    parser.add_option('-f', '--framerate', dest='videoFramerate', type='string', help='')
    parser.add_option('-v', '--vrate', dest='videoBitrate', type='string', help='')
    parser.add_option('-a', '--arate', dest='audioBitrate', type='string', help='')

    parser.add_option('-t', '--threads', dest='numThreads', type='int', help='')
    parser.set_default('threads', 2)

    (options, args) = parser.parse_args()
    err = False
    
    if not options.outputFile:
        print "Output file must be specified."
        err = True
        
    if not options.inputFile:
        print "Input file must be specified."
        err = True
        
    if not options.videoWidth:
        print "Video resolution (Width) must be specified."
        err = True
        
    if not options.videoHeight:
        print "Video resolution (Height) must be specified."
        err = True
        
    if not options.videoFramerate:
        print "Video framerate must be specified."
        err = True
        
    if not options.videoBitrate:
        print "Video bitrate must be specified."
        err = True
        
    if not options.audioBitrate:
        print "Audio bitrate must be specified."
        err = True

    if options.numThreads:
        if options.numThreads > 8:
            print "A maximum of 8 threads may be specified."
            
    if err == True:
        print
        parser.print_help()
    
    r_options['outputFile'] = options.outputFile
    r_options['inputFile'] = options.inputFile
    r_options['videoWidth'] = options.videoWidth
    r_options['videoHeight'] = options.videoHeight
    r_options['videoFramerate'] = options.videoFramerate
    r_options['videoBitrate'] = options.videoBitrate
    r_options['audioBitrate'] = options.audioBitrate
    
    for option in r_options:
        if r_options[option] == None:
            r_options[option] = ''
    
    if options.numThreads:
        r_options['numThreads'] = options.numThreads
    else:
        r_options['numThreads'] = '2' # XXX


def prepare_arguments(pass_num=None):
    arguments = Conf.Base.copy()
    
    if pass_num != None:
        if pass_num == '1':
            for arg in Conf.Pass1:
                if arg == 'options':
                    arguments[arg] = Conf.Base[arg] + ' ' + Conf.Pass1[arg]
                else:
                    arguments[arg] = Conf.Pass1[arg]
        elif pass_num == '2':
            for arg in Conf.Pass2:
                if arg == 'options':
                    arguments[arg] = Conf.Base[arg] + ' ' + Conf.Pass2[arg]
                else:
                    arguments[arg] = Conf.Pass2[arg]
        
    for arg in Conf.Configurable:
        if arg == 'options':
            arguments[arg] = arguments[arg] + ' ' + Conf.Configurable[arg]
        else:
            arguments[arg] = Conf.Configurable[arg]

    order = Conf.General['order'].split(',')

    ret = ''
    for oarg in order:
        if arguments.has_key(oarg):
            if arguments[oarg] != '':
                ret = ret + '-%s %s ' % (oarg, arguments[oarg])
            arguments.pop(oarg)

    opts = ''
    for arg in arguments:
        if arg == 'options':
            split_opts = arguments[arg].split(' ')
            for opt in split_opts:
                opts = opts + '%s ' % (opt)
        elif arguments[arg] != '':
            ret = ret + '-%s %s ' % (arg, arguments[arg])

    return ret + opts


# -vstats_file PIPE -> database
#
# Options / math to maintain aspect ratio
#
if __name__ == '__main__':

    options = {}
    commands = []
    parse_options(options)

    Conf = HandlerConfig('/z/www/upload.flixn.evilprojects.net/transcode/transcode_h264.conf', options)
    executable = Conf.General['command']
    
    if Conf.General['multipass'] == True:
        commands.insert(0, '%s %s' % (executable, prepare_arguments('1')))
        commands.insert(0, '%s %s' % (executable, prepare_arguments('2')))
    else:
        commands.insert(0, '%s %s' % (executable, prepare_arguments()))
 
    for i in range(len(commands)):
        command = commands.pop()
        print
        print command
        print
        p = subprocess.Popen(command, shell=True)
        p.wait()
