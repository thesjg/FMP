#!/usr/local/bin/python

import subprocess
import ConfigParser
from optparse import OptionParser
import sys

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
        self.Sections = ['command', 'general', 'video', 'audio', 'image']

        ConfigParser.ConfigParser.__init__(self)
        self.run()

    def run(self):
        self.read(self.ConfigFile)

        for section in self.Sections:
            if self.has_section(section) == 0:
                raise Exception('Missing configuration section: %s' % section)

        self.Command['executable'] = self.get('command', 'executable')
        self.Command['arguments'] = self.get('command', 'arguments')

        self.parse_section('general', self.General)
        self.parse_section('video', self.Video)
        self.parse_section('audio', self.Audio)
        self.parse_section('image', self.Image)

    def parse_section(self, name, ptr):
        for option in self.items(name):
            key, offset = option[1].split(',')
            ptr[key] = {}
            ptr[key]['name'] = option[0];
            ptr[key]['offset'] = int(offset);

class HandlerOutput:

    Handle = None

    ProcessedOutput = {}
    Munged = {}
    
    def __init__(self, handle):
        self.Handle = handle

        self.process_output()
        self.munge_metadata()
    
    def process_output(self):
        prev_newline = False
        firstline = True
        
        section = ''
        output = {}

        for line in self.Handle:
            line = line.strip()
            
            if firstline == True:
                section = line
                firstline = False
                continue
        
            if line == '':
                if prev_newline == True:
                    return
                
                prev_newline = True
                firstline = True
                self.ProcessedOutput[section] = output.copy()
                output = {}
                continue
        
            key, value = line.split(':', 1)
          
            prev_newline = False
            
            key = key.strip()
            value = value.strip()
            
            try:
                output[key].append(value)
            except:
                output[key] = []
                output[key].append(value)
                
    def munge_metadata(self):
        self.munge_section('general', 'General #0', Conf.General)
        self.munge_section('video', 'Video #0', Conf.Video)
        self.munge_section('audio', 'Audio #0', Conf.Audio)
        self.munge_section('image', 'Image #0', Conf.Image)
        
    def munge_section(self, short, idx, ptr):
        try:
            section = self.ProcessedOutput[idx]
        except:
            return None
        
        for key in ptr:
            try:
                name = ptr[key]['name']
                offset = ptr[key]['offset']
                value = section[key][offset]
            except:
                continue
            
            try:
                self.Munged[short].append({name: value})
            except:
                self.Munged[short] = []
                self.Munged[short].append({name: value})
    
def parse_options(r_options):
    usage = 'usage: %prog [options] arg';
    
    parser = OptionParser(usage);
    
    parser.add_option('-f', '--filename', dest='inputFile', type='string', help='')

    (options, args) = parser.parse_args()
    err = False
    
    if not options.inputFile:
        print "Input file must be specified."
        err = True
        
    if err == True:
        print
        parser.print_help()
        sys.exit()
    
    r_options['inputFile'] = options.inputFile
    
    
if __name__ == '__main__':

    options = {}
    parse_options(options)

    Conf = HandlerConfig('mediainfo.conf', options)

    command = "%s %s %s" % (Conf.Command['executable'],
                            Conf.Command['arguments'],
                            options['inputFile'])
    
    p = subprocess.Popen(command, shell=True, stdout=subprocess.PIPE)
    
    HandlerOutput(p.stdout)

    type = None
    munged = HandlerOutput.Munged

    output = []

    for i in munged['general']:
        for j in i.iterkeys():
            output.append("%s: %s" % (j, i[j]))

    if munged.has_key('video'):
        print 'video'
        for i in munged['video']:
            for j in i.iterkeys():
                output.append("video_%s: %s" % (j, i[j]))

    elif munged.has_key('audio'):
        print 'audio'
        for i in munged['audio']:
            for j in i.iterkeys():
                output.append("audio_%s: %s" % (j, i[j]))

    elif munged.has_key('image'):
        print 'image'
        for i in munged['image']:
            for j in i.iterkeys():
                output.append("image_%s: %s" % (j, i[j]))

    else:
        print 'unknown'
        
    for i in output:
        print i